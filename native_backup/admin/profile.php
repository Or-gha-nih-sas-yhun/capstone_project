<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('staff');

$page_title = 'Admin Profile';
$active_nav = 'profile';
$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_account') {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        db_execute("UPDATE users SET username=?, email=? WHERE id=?", 'ssi', $username, $email, $user_id);
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $username;
        log_activity('UPDATE_ADMIN_PROFILE', 'Profile', 'Admin updated account profile');
        set_flash('success', 'Profile updated successfully.');
    } elseif ($action === 'update_photo') {
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
                set_flash('danger', 'Profile photo must be JPG, PNG, or GIF.');
            } else {
                $path = upload_file($_FILES['photo'], 'profiles');
                if ($path) {
                    db_execute("UPDATE users SET photo=? WHERE id=?", 'si', $path, $user_id);
                    log_activity('UPDATE_ADMIN_PHOTO', 'Profile', 'Admin updated profile photo');
                    set_flash('success', 'Profile photo updated.');
                } else {
                    set_flash('danger', 'Unable to upload profile photo.');
                }
            }
        }
    } elseif ($action === 'remove_photo') {
        db_execute("UPDATE users SET photo=NULL WHERE id=?", 'i', $user_id);
        log_activity('REMOVE_ADMIN_PHOTO', 'Profile', 'Admin removed profile photo');
        set_flash('success', 'Profile photo removed.');
    } elseif ($action === 'change_password') {
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $conf = $_POST['confirm_password'] ?? '';
        $user = db_fetch_one("SELECT password FROM users WHERE id=?", 'i', $user_id);
        if (!$user || !password_verify($cur, $user['password'])) {
            set_flash('danger', 'Current password is incorrect.');
        } elseif ($new !== $conf) {
            set_flash('danger', 'New passwords do not match.');
        } elseif (strlen($new) < 6) {
            set_flash('danger', 'Password must be at least 6 characters.');
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            db_execute("UPDATE users SET password=? WHERE id=?", 'si', $hash, $user_id);
            log_activity('CHANGE_ADMIN_PASSWORD', 'Profile', 'Admin password changed');
            set_flash('success', 'Password changed successfully.');
        }
    }
    header('Location: '.BASE_URL.'/admin/profile.php'); exit;
}

$user = db_fetch_one("SELECT * FROM users WHERE id=?", 'i', $user_id);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="gov-page-heading">
  <div>
    <div class="eyebrow">Account Administration</div>
    <h1>Admin Profile</h1>
    <p>Maintain the signed-in government user account and password.</p>
  </div>
</div>

<div class="grid-2" style="max-width:900px;">
  <div class="card">
    <div class="card-header"><h5><i class="fas fa-camera" style="color:var(--primary);margin-right:8px;"></i>Profile Photo</h5></div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
        <div style="width:96px;height:96px;border-radius:50%;overflow:hidden;background:var(--primary);color:#fff;display:grid;place-items:center;font-size:34px;font-weight:800;border:4px solid #fff;box-shadow:var(--shadow);">
          <?php if (!empty($user['photo'])): ?>
            <img src="<?= UPLOAD_URL . htmlspecialchars($user['photo']) ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <?= strtoupper(substr($user['username'],0,1)) ?>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:220px;">
          <form method="POST" enctype="multipart/form-data" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
            <input type="hidden" name="action" value="update_photo">
            <div style="flex:1;min-width:190px;">
              <label class="form-label">Upload New Photo</label>
              <input type="file" name="photo" class="form-control" accept="image/*" required>
            </div>
            <button class="btn btn-primary"><i class="fas fa-upload"></i> Update</button>
          </form>
          <?php if (!empty($user['photo'])): ?>
          <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="action" value="remove_photo">
            <button class="btn btn-outline-secondary btn-sm" onclick="return confirm('Remove profile photo?')">
              <i class="fas fa-trash"></i> Remove Photo
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5><i class="fas fa-id-badge" style="color:var(--primary);margin-right:8px;"></i>Account Details</h5></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="update_account">
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
        </div>
        <button class="btn btn-primary"><i class="fas fa-save"></i> Save Profile</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5><i class="fas fa-lock" style="color:var(--primary);margin-right:8px;"></i>Password</h5></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="6">
        </div>
        <button class="btn btn-warning"><i class="fas fa-key"></i> Change Password</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
