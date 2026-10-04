<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
if ($_SESSION['user_role'] !== 'resident') { header('Location:'.BASE_URL.'/admin/dashboard.php'); exit; }

$page_title = 'My Profile';
$active_nav = 'profile';
$res_id     = (int)($_SESSION['resident_id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];

if (!$res_id) {
    $linked = db_fetch_one("SELECT resident_id FROM users WHERE id=?", 'i', $user_id);
    $res_id = (int)($linked['resident_id'] ?? 0);
    $_SESSION['resident_id'] = $res_id;
}

if (!$res_id) {
    set_flash('danger', 'Your account is not linked to a resident profile. Please contact the barangay office.');
    header('Location:'.BASE_URL.'/resident/dashboard.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        if (!is_letters_only($_POST['first_name']) || !is_letters_only($_POST['last_name']) || !is_letters_only($_POST['middle_name'])) {
            set_flash('danger', 'First, middle, and last names must contain only letters.');
            header('Location:'.BASE_URL.'/resident/profile.php'); exit;
        }
        db_execute(
            "UPDATE residents SET first_name=?,middle_name=?,last_name=?,contact_number=?,
             address=?,purok=?,occupation=?,voter_status=?,civil_status=? WHERE id=?",
            'sssssssssi',
            sanitize($_POST['first_name']), sanitize($_POST['middle_name']),
            sanitize($_POST['last_name']),  sanitize($_POST['contact_number']),
            sanitize($_POST['address']),    sanitize($_POST['purok']),
            sanitize($_POST['occupation']), sanitize($_POST['voter_status']),
            sanitize($_POST['civil_status']), $res_id
        );
        $_SESSION['full_name'] = sanitize($_POST['first_name']).' '.sanitize($_POST['last_name']);
        log_activity('UPDATE_PROFILE','Profile','Resident updated profile');
        set_flash('success','Profile updated successfully.');
    } elseif ($action === 'change_password') {
        $cur  = $_POST['current_password'] ?? '';
        $new  = $_POST['new_password']     ?? '';
        $conf = $_POST['confirm_password'] ?? '';
        $user = db_fetch_one("SELECT password FROM users WHERE id=?", 'i', $user_id);
        if (!password_verify($cur, $user['password'])) {
            set_flash('danger','Current password is incorrect.');
        } elseif ($new !== $conf) {
            set_flash('danger','New passwords do not match.');
        } elseif (strlen($new) < 6) {
            set_flash('danger','Password must be at least 6 characters.');
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            db_execute("UPDATE users SET password=? WHERE id=?", 'si', $hash, $user_id);
            log_activity('CHANGE_PASSWORD','Profile','Password changed');
            set_flash('success','Password changed successfully.');
        }
    } elseif ($action === 'update_photo') {
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
                set_flash('danger', 'Profile photo must be JPG, PNG, or GIF.');
            } else {
                $path = upload_file($_FILES['photo'], 'profiles');
                if ($path) {
                    db_execute("UPDATE residents SET photo=? WHERE id=?", 'si', $path, $res_id);
                    log_activity('UPDATE_PROFILE_PHOTO','Profile','Resident updated profile photo');
                    set_flash('success','Profile photo updated.');
                } else {
                    set_flash('danger','Unable to upload profile photo.');
                }
            }
        }
    } elseif ($action === 'remove_photo') {
        db_execute("UPDATE residents SET photo=NULL WHERE id=?", 'i', $res_id);
        log_activity('REMOVE_PROFILE_PHOTO','Profile','Resident removed profile photo');
        set_flash('success','Profile photo removed.');
    }
    header('Location:'.BASE_URL.'/resident/profile.php'); exit;
}

$resident = db_fetch_one("SELECT r.*, u.username, u.email AS account_email
    FROM residents r JOIN users u ON u.resident_id=r.id WHERE r.id=?", 'i', $res_id);

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px;margin:0 auto;">

  <!-- Profile Card -->
  <div class="card" style="grid-column:span 2;">
    <div style=""></div>
    <br>
    <br>
    <div style="padding:0 28px 28px;margin-top:-36px;">
      <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));
                  border:4px solid #fff;display:flex;align-items:center;justify-content:center;
                  font-size:28px;font-weight:800;color:#fff;margin-bottom:12px;overflow:hidden;">
        <?php if (!empty($resident['photo'])): ?>
          <img src="<?= UPLOAD_URL . htmlspecialchars($resident['photo']) ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <?= strtoupper(substr($resident['first_name'],0,1)) ?>
        <?php endif; ?>
      </div>
      <div style="font-size:20px;font-weight:800;"><?= htmlspecialchars($resident['first_name'].' '.$resident['last_name']) ?></div>
      <div style="color:#6b7280;font-size:13px;">@<?= htmlspecialchars($resident['username']) ?></div>
      <div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:14px;font-size:13px;color:#374151;">
        <span><i class="fas fa-map-marker-alt" style="color:var(--primary);margin-right:4px;"></i><?= htmlspecialchars($resident['address']) ?></span>
        <span><i class="fas fa-phone" style="color:var(--primary);margin-right:4px;"></i><?= htmlspecialchars($resident['contact_number']??'—') ?></span>
        <span><i class="fas fa-birthday-cake" style="color:var(--primary);margin-right:4px;"></i><?= format_date($resident['birthdate']) ?></span>
        <span><i class="fas fa-user" style="color:var(--primary);margin-right:4px;"></i><?= calculate_age($resident['birthdate']) ?> yrs old</span>
      </div>
    </div>
  </div>

  <div class="card" style="grid-column:span 2;">
    <div class="card-header"><h5><i class="fas fa-camera" style="color:var(--primary);margin-right:8px;"></i>Profile Photo</h5></div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
        <input type="hidden" name="action" value="update_photo">
        <div style="flex:1;min-width:220px;">
          <label class="form-label">Upload New Photo</label>
          <input type="file" name="photo" class="form-control" accept="image/*" required>
        </div>
        <button class="btn btn-primary"><i class="fas fa-upload"></i> Update Photo</button>
      </form>
      <?php if (!empty($resident['photo'])): ?>
      <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="action" value="remove_photo">
        <button class="btn btn-outline-secondary btn-sm" onclick="return confirm('Remove profile photo?')">
          <i class="fas fa-trash"></i> Remove Photo
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Update Info Form -->
  <div class="card">
    <div class="card-header"><h5><i class="fas fa-user-edit" style="color:var(--primary);margin-right:8px;"></i>Edit Profile</h5></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($resident['first_name']) ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($resident['last_name']) ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Middle Name</label>
          <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($resident['middle_name']??'') ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
        </div>
        <div class="form-group">
          <label class="form-label">Contact Number</label>
          <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($resident['contact_number']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" required value="<?= htmlspecialchars($resident['address']) ?>">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Purok</label>
            <input type="text" name="purok" class="form-control" value="<?= htmlspecialchars($resident['purok']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($resident['occupation']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Civil Status</label>
            <select name="civil_status" class="form-select">
              <?php foreach (['Single','Married','Widowed','Separated'] as $cs): ?>
              <option <?= $resident['civil_status']===$cs?'selected':'' ?>><?= $cs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Voter Status</label>
            <select name="voter_status" class="form-select">
              <?php foreach (['Not Registered','Registered'] as $vs): ?>
              <option <?= $resident['voter_status']===$vs?'selected':'' ?>><?= $vs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Update Profile</button>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card">
    <div class="card-header"><h5><i class="fas fa-lock" style="color:var(--primary);margin-right:8px;"></i>Change Password</h5></div>
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
        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-key"></i> Change Password</button>
      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
