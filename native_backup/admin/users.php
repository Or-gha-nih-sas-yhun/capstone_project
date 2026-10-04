<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
require_role('admin');

$page_title = 'User Management';
$active_nav = 'users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? 'resident');
    $password = $_POST['password'] ?? '';

    if (!$password) {
        set_flash('danger', 'Password is required for new users.');
        header('Location:' . BASE_URL . '/admin/users.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    db_insert(
        "INSERT INTO users (username,email,password,role,status) VALUES (?,?,?,?,?)",
        'sssss',
        $username,
        $email,
        $hash,
        $role,
        'active'
    );
    log_activity('ADD_USER', 'Users', "Created user $username");
    set_flash('success', 'User created successfully.');
    header('Location:' . BASE_URL . '/admin/users.php');
    exit;
}

if (isset($_GET['approve'])) {
    $uid = (int) $_GET['approve'];
    db_execute("UPDATE users SET status='active' WHERE id=?", 'i', $uid);
    log_activity('APPROVE_USER', 'Users', "Approved user ID $uid");
    set_flash('success', 'User approved/activated successfully.');
    header('Location:' . BASE_URL . '/admin/users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $did = (int) $_GET['delete'];
    if ($did !== (int) $_SESSION['user_id']) {
        $uid = (int) $_SESSION['user_id'];
        db_execute("UPDATE users SET status='suspended', archived_at=NOW(), archived_by=? WHERE id=?", 'ii', $uid, $did);
        log_activity('ARCHIVE_USER', 'Users', "Archived user ID $did");
        set_flash('success', 'User archived/suspended. You can restore it from Archive.');
    } else {
        set_flash('danger', 'You cannot suspend your own account.');
    }
    header('Location:' . BASE_URL . '/admin/users.php');
    exit;
}

$users = db_fetch_all(
    "SELECT u.*, CONCAT(r.last_name, ', ', r.first_name, ' ', COALESCE(r.middle_name,'')) AS resident_name,
            r.contact_number, r.purok
     FROM users u
     LEFT JOIN residents r ON u.resident_id=r.id
     WHERE u.archived_at IS NULL AND u.status <> 'suspended'
     ORDER BY FIELD(u.role,'admin','staff','resident'), u.username"
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="gov-page-heading">
    <div>
        <div class="eyebrow">Account Registry</div>
        <h1>User Management</h1>
        <p>Verified system and resident accounts only. Profile changes are handled by the account owner.</p>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
    <button class="btn btn-primary" onclick="openModal('userModal')">
        <i class="fas fa-user-plus"></i> Add User
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-user-shield" style="color:var(--primary);margin-right:8px;"></i>System Users
            (<?= count($users) ?>)</h5>
    </div>
    <div class="table-wrapper">
        <table class="table gov-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Account</th>
                    <th>Role</th>
                    <th>Linked Resident</th>
                    <th>Contact / Purok</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding:40px;">No active users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong><br><small
                                class="text-muted"><?= htmlspecialchars($u['email']) ?></small></td>
                        <td>
                            <?php $rc = ['admin' => 'primary', 'staff' => 'info', 'resident' => 'secondary'][$u['role']] ?? 'secondary'; ?>
                            <span class="badge bg-<?= $rc ?>"><?= ucfirst($u['role']) ?></span>
                        </td>
                        <td><?= $u['resident_name'] ? htmlspecialchars($u['resident_name']) : '<span class="text-muted">Not linked</span>' ?>
                        </td>
                        <td><small><?= htmlspecialchars($u['contact_number'] ?? '') ?><br><?= htmlspecialchars($u['purok'] ?? '') ?></small>
                        </td>
                        <td><?= status_badge($u['status']) ?></td>
                        <td><small><?= format_date($u['created_at']) ?></small></td>
                        <td style="text-align:right;">
                            <?php if ($u['id'] !== (int) $_SESSION['user_id']): ?>
                                <?php if ($u['status'] === 'inactive'): ?>
                                    <a href="?approve=<?= $u['id'] ?>" class="btn btn-success btn-sm"
                                        onclick="return confirm('Approve and activate this user?')">
                                        <i class="fas fa-check"></i> Approve
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Archive and suspend this user?')">
                                    <i class="fas fa-box-archive"></i> Archive
                                </a>
                            <?php else: ?>
                                <span class="text-muted fs-sm">Current account</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="userModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>Add User</h5>
            <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <?php foreach (['resident', 'staff', 'admin'] as $role): ?>
                            <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6"
                        placeholder="Min 6 characters">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>