<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
if ($_SESSION['user_role'] !== 'resident') {
  header('Location:' . BASE_URL . '/admin/dashboard.php');
  exit;
}

$page_title = 'My Requests';
$active_nav = 'my_requests';
$res_id = (int) $_SESSION['resident_id'];
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 10;
$total = (int) db_fetch_one("SELECT COUNT(*) c FROM requests WHERE resident_id=? AND archived_at IS NULL", 'i', $res_id)['c'];
$pg = paginate($total, $per_page, $page);

$requests = db_fetch_all(
  "SELECT r.*, c.name AS cert_name, c.fee, p.payment_status
     FROM requests r
     JOIN certificates c ON r.certificate_id=c.id
     LEFT JOIN payments p ON p.request_id=r.id
     WHERE r.resident_id=? AND r.archived_at IS NULL ORDER BY r.requested_at DESC LIMIT ? OFFSET ?",
  'iii',
  $res_id,
  $per_page,
  $pg['offset']
);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>My Requests (<?= $total ?>)</h5>
    <a href="<?= BASE_URL ?>/resident/request.php" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> New Request
    </a>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Tracking #</th>
          <th>Document</th>
          <th>Purpose</th>
          <th>Fee</th>
          <th>Payment</th>
          <th>Filed</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="8" class="text-center" style="padding:40px;">
              <div style="font-size:40px;margin-bottom:10px;">📭</div>
              <p class="text-muted">No requests yet.</p>
              <a href="<?= BASE_URL ?>/resident/request.php" class="btn btn-primary" style="margin-top:8px;">
                <i class="fas fa-plus"></i> Make a Request
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr>
              <td><code style="font-size:11px;"><?= htmlspecialchars($r['tracking_number']) ?></code></td>
              <td style="font-weight:600;"><?= htmlspecialchars($r['cert_name']) ?></td>
              <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                title="<?= htmlspecialchars($r['purpose']) ?>"><?= htmlspecialchars($r['purpose']) ?></td>
              <td><?= $r['fee'] > 0 ? '₱' . number_format($r['fee'], 2) : '<span class="badge bg-success">FREE</span>' ?></td>
              <td><?= status_badge($r['payment_status'] ?? 'unpaid') ?></td>
              <td><small><?= format_datetime($r['requested_at']) ?></small></td>
              <td><?= status_badge($r['status']) ?></td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="<?= BASE_URL ?>/track.php?tracking=<?= urlencode($r['tracking_number']) ?>"
                    class="btn btn-outline-secondary btn-sm" target="_blank" title="Track">
                    <i class="fas fa-search-location"></i>
                  </a>
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
        <?php for ($i = 1; $i <= $pg['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>" class="page-btn <?= $i === $pg['current'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>