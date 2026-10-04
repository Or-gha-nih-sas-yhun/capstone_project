<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
if ($_SESSION['user_role'] !== 'resident') {
    header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
}
// Resident dashboard removed — redirect to My Requests
header('Location: '.BASE_URL.'/resident/my_requests.php'); exit;
$res_id     = (int)$_SESSION['resident_id'];

$resident = db_fetch_one("SELECT * FROM residents WHERE id=?", 'i', $res_id);
$my_requests = db_fetch_all(
    "SELECT r.*, c.name AS cert_name, c.fee, p.payment_status
     FROM requests r
     JOIN certificates c ON r.certificate_id=c.id
     LEFT JOIN payments p ON p.request_id=r.id
     WHERE r.resident_id=? ORDER BY r.requested_at DESC LIMIT 5",
    'i', $res_id
);
$counts = db_fetch_one(
    "SELECT
       COUNT(*) AS total,
       SUM(status='pending') AS pending,
       SUM(status='approved') AS approved,
       SUM(status='released') AS released,
       SUM(status='rejected') AS rejected
     FROM requests WHERE resident_id=?", 'i', $res_id
);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Welcome Banner -->
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));
            border-radius:14px;padding:28px 32px;color:#fff;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
  <div>
    <div style="font-size:13px;opacity:.75;margin-bottom:4px;">Welcome back,</div>
    <h2 style="font-size:22px;font-weight:800;margin-bottom:6px;">
      <?= htmlspecialchars($resident['first_name'].' '.$resident['last_name']) ?>
    </h2>
    <p style="opacity:.8;font-size:13.5px;">
      <i class="fas fa-map-marker-alt"></i>
      <?= htmlspecialchars($resident['address']) ?>
      <?= $resident['purok'] ? ' · '.htmlspecialchars($resident['purok']) : '' ?>
    </p>
  </div>
  <a href="<?= BASE_URL ?>/resident/request.php" class="btn" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
    <i class="fas fa-plus-circle"></i> New Request
  </a>
</div>

<!-- Stats -->
<div class="grid-4" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
    <div><div class="stat-value"><?= $counts['total'] ?? 0 ?></div><div class="stat-label">Total Requests</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
    <div><div class="stat-value"><?= $counts['pending'] ?? 0 ?></div><div class="stat-label">Pending</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
    <div><div class="stat-value"><?= $counts['approved'] ?? 0 ?></div><div class="stat-label">Approved</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-box-open"></i></div>
    <div><div class="stat-value"><?= $counts['released'] ?? 0 ?></div><div class="stat-label">Released</div></div>
  </div>
</div>

<!-- Recent Requests -->
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-list" style="color:var(--primary);margin-right:8px;"></i>My Recent Requests</h5>
    <a href="<?= BASE_URL ?>/resident/my_requests.php" class="btn btn-outline-primary btn-sm">View All</a>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr><th>Tracking #</th><th>Document</th><th>Fee</th><th>Payment</th><th>Date</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($my_requests)): ?>
        <tr>
          <td colspan="7" class="text-center" style="padding:40px;">
            <div style="font-size:36px;margin-bottom:12px;">📄</div>
            <p style="color:#6b7280;">No requests yet.</p>
            <a href="<?= BASE_URL ?>/resident/request.php" class="btn btn-primary" style="margin-top:8px;">
              <i class="fas fa-plus"></i> Make a Request
            </a>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($my_requests as $r): ?>
        <tr>
          <td><code style="font-size:11px;"><?= htmlspecialchars($r['tracking_number']) ?></code></td>
          <td style="font-weight:600;"><?= htmlspecialchars($r['cert_name']) ?></td>
          <td>₱<?= number_format($r['fee'],2) ?></td>
          <td><?= status_badge($r['payment_status'] ?? 'unpaid') ?></td>
          <td><small><?= format_datetime($r['requested_at']) ?></small></td>
          <td><?= status_badge($r['status']) ?></td>
          <td>
            <?php if (in_array($r['status'],['approved','released'])): ?>
            <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $r['id'] ?>"
               target="_blank" class="btn btn-success btn-sm"><i class="fas fa-print"></i> Print</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
