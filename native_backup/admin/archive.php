<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('admin');

$page_title = 'Archive';
$active_nav = 'archive';
$type = sanitize($_GET['type'] ?? 'residents');
$allowed = ['residents','requests','certificates','users'];
if (!in_array($type, $allowed, true)) $type = 'residents';

if (isset($_GET['restore'], $_GET['type'])) {
    $id = (int)$_GET['restore'];
    $restore_type = sanitize($_GET['type']);

    if ($restore_type === 'residents') {
        db_execute("UPDATE residents SET status='active', archived_at=NULL, archived_by=NULL WHERE id=?", 'i', $id);
        db_execute("UPDATE users SET status='active', archived_at=NULL, archived_by=NULL WHERE resident_id=?", 'i', $id);
        log_activity('RESTORE_RESIDENT', 'Archive', "Restored resident ID $id");
    } elseif ($restore_type === 'requests') {
        db_execute("UPDATE requests SET archived_at=NULL, archived_by=NULL WHERE id=?", 'i', $id);
        log_activity('RESTORE_REQUEST', 'Archive', "Restored request ID $id");
    } elseif ($restore_type === 'certificates') {
        db_execute("UPDATE certificates SET status='active', archived_at=NULL, archived_by=NULL WHERE id=?", 'i', $id);
        log_activity('RESTORE_CERTIFICATE', 'Archive', "Restored certificate ID $id");
    } elseif ($restore_type === 'users') {
        db_execute("UPDATE users SET status='active', archived_at=NULL, archived_by=NULL WHERE id=?", 'i', $id);
        log_activity('RESTORE_USER', 'Archive', "Restored user ID $id");
    }

    set_flash('success', 'Archived record restored successfully.');
    header('Location: '.BASE_URL.'/admin/archive.php?type='.$restore_type); exit;
}

$rows = [];
if ($type === 'residents') {
    $rows = db_fetch_all("SELECT id, CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name,'')) AS title,
        email AS detail, status, archived_at FROM residents
        WHERE status='inactive' OR archived_at IS NOT NULL ORDER BY archived_at DESC, updated_at DESC");
} elseif ($type === 'requests') {
    $rows = db_fetch_all("SELECT r.id, r.tracking_number AS title,
        CONCAT(res.first_name, ' ', res.last_name, ' - ', c.name) AS detail, r.status, r.archived_at
        FROM requests r
        JOIN residents res ON r.resident_id=res.id
        JOIN certificates c ON r.certificate_id=c.id
        WHERE r.archived_at IS NOT NULL ORDER BY r.archived_at DESC");
} elseif ($type === 'certificates') {
    $rows = db_fetch_all("SELECT id, name AS title, category AS detail, status, archived_at
        FROM certificates WHERE status='inactive' OR archived_at IS NOT NULL ORDER BY archived_at DESC, name");
} else {
    $rows = db_fetch_all("SELECT u.id, u.username AS title,
        CONCAT(u.email, ' - ', u.role) AS detail, u.status, u.archived_at
        FROM users u WHERE u.status IN ('inactive','suspended') OR u.archived_at IS NOT NULL
        ORDER BY u.archived_at DESC, u.username");
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="gov-page-heading">
  <div>
    <div class="eyebrow">Records Retention</div>
    <h1>Archive</h1>
    <p>Inactive and deleted records are retained here for audit review and restoration.</p>
  </div>
</div>

<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach ($allowed as $tab): ?>
    <a href="?type=<?= $tab ?>" class="btn btn-sm <?= $type===$tab?'btn-primary':'btn-outline-secondary' ?>">
      <i class="fas fa-<?= $tab==='requests'?'file-alt':($tab==='users'?'user-shield':($tab==='certificates'?'certificate':'users')) ?>"></i>
      <?= ucfirst($tab) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-box-archive" style="color:var(--primary);margin-right:8px;"></i><?= ucfirst($type) ?> Archive (<?= count($rows) ?>)</h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr><th>#</th><th>Record</th><th>Details</th><th>Status</th><th>Archived</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No archived records found.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $row): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
          <td><?= htmlspecialchars($row['detail'] ?? '') ?></td>
          <td><?= status_badge($row['status']) ?></td>
          <td><small><?= $row['archived_at'] ? format_datetime($row['archived_at']) : 'Inactive legacy record' ?></small></td>
          <td>
            <a href="?type=<?= $type ?>&restore=<?= $row['id'] ?>" class="btn btn-success btn-sm"
               onclick="return confirm('Restore this archived record?')">
              <i class="fas fa-rotate-left"></i> Restore
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
