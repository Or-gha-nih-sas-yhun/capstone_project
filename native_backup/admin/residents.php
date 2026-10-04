<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('staff');

$page_title = 'Manage Residents';
$active_nav = 'residents';

// --- Save / Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['resident_id'] ?? 0);
    $fields = [
        'first_name'        => sanitize($_POST['first_name']       ?? ''),
        'middle_name'       => sanitize($_POST['middle_name']      ?? ''),
        'last_name'         => sanitize($_POST['last_name']        ?? ''),
        'gender'            => sanitize($_POST['gender']           ?? ''),
        'birthdate'         => sanitize($_POST['birthdate']        ?? ''),
        'civil_status'      => sanitize($_POST['civil_status']     ?? ''),
        'contact_number'    => sanitize($_POST['contact_number']   ?? ''),
        'email'             => sanitize($_POST['email']            ?? ''),
        'address'           => sanitize($_POST['address']          ?? ''),
        'purok'             => sanitize($_POST['purok']            ?? ''),
        'occupation'        => sanitize($_POST['occupation']       ?? ''),
        'voter_status'      => sanitize($_POST['voter_status']     ?? 'Not Registered'),
        'years_of_residency'=> (int)($_POST['years_of_residency'] ?? 0),
    ];

    if (!is_letters_only($fields['first_name']) || !is_letters_only($fields['last_name']) || !is_letters_only($fields['middle_name'])) {
        set_flash('danger', 'First, middle, and last names must contain only letters.');
        header('Location: '.BASE_URL.'/admin/residents.php'); exit;
    }

    if ($id) {
        db_execute(
            "UPDATE residents SET first_name=?,middle_name=?,last_name=?,gender=?,birthdate=?,
             civil_status=?,contact_number=?,email=?,address=?,purok=?,occupation=?,
             voter_status=?,years_of_residency=? WHERE id=?",
            'ssssssssssssii',
            ...[...array_values($fields), $id]
        );
        log_activity('UPDATE_RESIDENT','Residents',"Updated resident ID $id");
        set_flash('success','Resident updated successfully.');
    } else {
        db_insert(
            "INSERT INTO residents (first_name,middle_name,last_name,gender,birthdate,
             civil_status,contact_number,email,address,purok,occupation,voter_status,years_of_residency)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
            'ssssssssssssi',
            ...array_values($fields)
        );
        log_activity('ADD_RESIDENT','Residents','Added new resident');
        set_flash('success','Resident added successfully.');
    }
    header('Location: '.BASE_URL.'/admin/residents.php'); exit;
}

// --- Approve ---
if (isset($_GET['approve'])) {
    $aid = (int)$_GET['approve'];
    db_execute("UPDATE users SET status='active' WHERE resident_id=?", 'i', $aid);
    log_activity('APPROVE_RESIDENT','Residents',"Approved resident user linked to resident ID $aid");
    set_flash('success','Resident registry approved successfully.');
    header('Location: '.BASE_URL.'/admin/residents.php?status=pending'); exit;
}

// --- Reject ---
if (isset($_GET['reject'])) {
    $rid = (int)$_GET['reject'];
    $uid = (int)$_SESSION['user_id'];
    db_execute("UPDATE residents SET status='inactive', archived_at=NOW(), archived_by=? WHERE id=?", 'ii', $uid, $rid);
    db_execute("UPDATE users SET status='inactive', archived_at=NOW(), archived_by=? WHERE resident_id=?", 'ii', $uid, $rid);
    log_activity('REJECT_RESIDENT','Residents',"Rejected/deactivated resident ID $rid");
    set_flash('success','Resident registration rejected.');
    header('Location: '.BASE_URL.'/admin/residents.php?status=pending'); exit;
}

// --- Delete ---
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $uid = (int)$_SESSION['user_id'];
    db_execute("UPDATE residents SET status='inactive', archived_at=NOW(), archived_by=? WHERE id=?", 'ii', $uid, $did);
    db_execute("UPDATE users SET status='inactive', archived_at=NOW(), archived_by=? WHERE resident_id=?", 'ii', $uid, $did);
    log_activity('ARCHIVE_RESIDENT','Residents',"Archived resident ID $did");
    set_flash('success','Resident archived safely. You can restore it from Archive.');
    header('Location: '.BASE_URL.'/admin/residents.php'); exit;
}

// --- Fetch for editing ---
$edit = null;
if (isset($_GET['edit'])) {
    $edit = db_fetch_one("SELECT * FROM residents WHERE id=?", 'i', (int)$_GET['edit']);
}

// --- List ---
$search   = sanitize($_GET['search'] ?? '');
$page     = max(1,(int)($_GET['page'] ?? 1));
$per_page = 12;

$status_filter = sanitize($_GET['status'] ?? 'active');
if ($status_filter === 'pending') {
    $where = "WHERE r.status='active' AND r.archived_at IS NULL AND u.status='inactive'";
} else {
    $where = "WHERE r.status='active' AND r.archived_at IS NULL AND (u.status='active' OR u.id IS NULL)";
}

$params   = []; $types = '';
if ($search) {
    $like = "%$search%";
    $where .= " AND (r.first_name LIKE ? OR r.last_name LIKE ? OR r.email LIKE ? OR r.contact_number LIKE ?)";
    $params = [$like,$like,$like,$like]; $types = 'ssss';
}

$total = (int)db_fetch_one(
    "SELECT COUNT(*) c FROM residents r LEFT JOIN users u ON u.resident_id=r.id $where",
    $types, ...$params
)['c'];

$pg    = paginate($total, $per_page, $page);
$params_paged = array_merge($params,[$per_page,$pg['offset']]); $types_paged = $types.'ii';

$residents = db_fetch_all(
    "SELECT r.*, u.username FROM residents r LEFT JOIN users u ON u.resident_id=r.id
     $where ORDER BY r.last_name,r.first_name LIMIT ? OFFSET ?",
    $types_paged, ...$params_paged
);

$pending_count = (int)db_fetch_one(
    "SELECT COUNT(*) c FROM residents r JOIN users u ON u.resident_id=r.id WHERE r.status='active' AND r.archived_at IS NULL AND u.status='inactive'"
)['c'];

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <form method="GET" style="display:flex;gap:8px;flex:1;max-width:400px;">
    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
    <input type="text" name="search" class="form-control" placeholder="Search by name, email, contact…"
           value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    <?php if ($search): ?><a href="?status=<?= htmlspecialchars($status_filter) ?>" class="btn btn-secondary">Reset</a><?php endif; ?>
  </form>
  <button class="btn btn-primary" onclick="openModal('residentModal')">
    <i class="fas fa-plus"></i> Add Resident
  </button>
</div>

<div class="card">
  <div class="card-header" style="flex-direction:column;align-items:stretch;gap:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h5><i class="fas fa-users" style="color:var(--primary);margin-right:8px;"></i>
          Residents <span style="font-size:13px;font-weight:400;color:#6b7280;">(<?= $total ?>)</span></h5>
    </div>
    <!-- Tabs/Filters -->
    <div style="display:flex;gap:8px;border-bottom:1px solid var(--gray-light);padding-bottom:8px;">
      <a href="?status=active<?= $search?'&search='.urlencode($search):'' ?>" class="btn btn-sm <?= $status_filter === 'active' ? 'btn-primary' : 'btn-outline-secondary' ?>" style="border-radius:20px; padding:6px 16px;">
        <i class="fas fa-user-check"></i> Active Residents
      </a>
      <a href="?status=pending<?= $search?'&search='.urlencode($search):'' ?>" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>" style="border-radius:20px; padding:6px 16px; position:relative;">
        <i class="fas fa-user-clock"></i> Pending Approval
        <?php if ($pending_count > 0): ?>
          <span class="badge bg-danger" style="font-size:9px; padding:2px 6px; margin-left:4px;"><?= $pending_count ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Full Name</th><th>Gender</th><th>Age</th>
          <th>Civil Status</th><th>Contact</th><th>Purok</th>
          <th>Voter</th><th>Account</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($residents)): ?>
        <tr><td colspan="10" class="text-center text-muted" style="padding:40px;">No residents found.</td></tr>
        <?php else: ?>
        <?php foreach ($residents as $i => $r): ?>
        <tr>
          <td><?= $pg['offset']+$i+1 ?></td>
          <td>
            <div style="font-weight:600;"><?= htmlspecialchars($r['last_name'].', '.$r['first_name'].' '.($r['middle_name']??'')) ?></div>
            <small class="text-muted"><?= htmlspecialchars($r['email'] ?? '') ?></small>
          </td>
          <td><?= htmlspecialchars($r['gender']) ?></td>
          <td><?= calculate_age($r['birthdate']) ?></td>
          <td><?= htmlspecialchars($r['civil_status']) ?></td>
          <td><?= htmlspecialchars($r['contact_number'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['purok'] ?? '—') ?></td>
          <td><?= $r['voter_status']==='Registered'
                ? '<span class="badge bg-success">Registered</span>'
                : '<span class="badge bg-secondary">No</span>' ?></td>
          <td><?= $r['username']
                ? '<span class="badge bg-primary"><i class="fas fa-check"></i> Yes</span>'
                : '<span class="badge bg-secondary">None</span>' ?></td>
          <td>
            <div style="display:flex;gap:4px;">
              <?php if ($status_filter === 'pending'): ?>
                <a href="?approve=<?= $r['id'] ?>" class="btn btn-success btn-sm"
                   onclick="return confirm('Approve this resident registration?')">
                  <i class="fas fa-check-circle"></i> Approve
                </a>
                <a href="?reject=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Reject and remove this resident registration?')">
                  <i class="fas fa-times-circle"></i> Reject
                </a>
              <?php else: ?>
                <a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                <a href="?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Remove this resident?')"><i class="fas fa-trash"></i></a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pg['total_pages'] > 1): ?>
  <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
    <div class="pagination">
      <?php for ($i=1;$i<=$pg['total_pages'];$i++): ?>
      <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&status=<?=$status_filter?>" class="page-btn <?=$i===$pg['current']?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay <?= ($edit||isset($_POST['resident_id']))?'show':'' ?>" id="residentModal">
  <div class="modal-box" style="max-width:700px;">
    <div class="modal-header">
      <h5><?= $edit?'Edit Resident':'Add New Resident' ?></h5>
      <button class="modal-close" onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/residents.php\'':'closeModal(\'residentModal\')' ?>">&times;</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="resident_id" value="<?= $edit['id'] ?? 0 ?>">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($edit['first_name']??'') ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($edit['last_name']??'') ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Middle Name</label>
            <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($edit['middle_name']??'') ?>" pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
          </div>
          <div class="form-group">
            <label class="form-label">Birthdate *</label>
            <input type="date" name="birthdate" class="form-control" required value="<?= $edit['birthdate']??'' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-select" required>
              <?php foreach (['Male','Female','Other'] as $g): ?>
              <option <?= ($edit['gender']??'')===$g?'selected':'' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Civil Status *</label>
            <select name="civil_status" class="form-select" required>
              <?php foreach (['Single','Married','Widowed','Separated'] as $cs): ?>
              <option <?= ($edit['civil_status']??'')===$cs?'selected':'' ?>><?= $cs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($edit['contact_number']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit['email']??'') ?>">
          </div>
          <div class="form-group" style="grid-column:span 2;">
            <label class="form-label">Complete Address *</label>
            <input type="text" name="address" class="form-control" required value="<?= htmlspecialchars($edit['address']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Purok</label>
            <input type="text" name="purok" class="form-control" value="<?= htmlspecialchars($edit['purok']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($edit['occupation']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Voter Status</label>
            <select name="voter_status" class="form-select">
              <?php foreach (['Not Registered','Registered'] as $vs): ?>
              <option <?= ($edit['voter_status']??'')===$vs?'selected':'' ?>><?= $vs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Years of Residency</label>
            <input type="number" name="years_of_residency" class="form-control" min="0" value="<?= $edit['years_of_residency']??0 ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
          onclick="<?= $edit?'window.location=\''.BASE_URL.'/admin/residents.php\'':'closeModal(\'residentModal\')' ?>">
          Cancel
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= $edit?'Update':'Save' ?> Resident
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
