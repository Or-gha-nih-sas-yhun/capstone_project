<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login(); require_role('admin');

$page_title = 'Reports';
$active_nav = 'reports';

$month = sanitize($_GET['month'] ?? date('Y-m'));
$start = $month . '-01';
$end = date('Y-m-t', strtotime($start));
$from = $start . ' 00:00:00';
$to = $end . ' 23:59:59';

$transactions = db_fetch_all(
    "SELECT r.tracking_number, r.status, r.requested_at,
            CONCAT(res.last_name, ', ', res.first_name) AS resident_name,
            c.name AS cert_name, c.category, c.fee,
            COALESCE(p.payment_status,'unpaid') AS payment_status,
            COALESCE(p.amount,c.fee) AS amount
     FROM requests r
     JOIN residents res ON r.resident_id=res.id
     JOIN certificates c ON r.certificate_id=c.id
     LEFT JOIN payments p ON p.request_id=r.id
     WHERE r.archived_at IS NULL AND r.requested_at BETWEEN ? AND ?
     ORDER BY r.requested_at DESC",
    'ss', $from, $to
);

$by_cert = db_fetch_all(
    "SELECT c.name, c.category, COUNT(r.id) AS total,
            SUM(r.status='approved') AS approved,
            SUM(r.status='released') AS released,
            COALESCE(SUM(CASE WHEN p.payment_status='paid' THEN p.amount ELSE 0 END),0) AS collections
     FROM certificates c
     LEFT JOIN requests r ON r.certificate_id=c.id AND r.archived_at IS NULL AND r.requested_at BETWEEN ? AND ?
     LEFT JOIN payments p ON p.request_id=r.id
     GROUP BY c.id
     HAVING total > 0
     ORDER BY c.category, c.name",
    'ss', $from, $to
);

$total_amount = 0;
foreach ($transactions as $transaction) {
    if ($transaction['payment_status'] === 'paid') {
        $total_amount += (float)$transaction['amount'];
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transactions_report_' . $month . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tracking No.', 'Resident', 'Certificate', 'Date Filed', 'Status', 'Payment', 'Amount']);
    foreach ($transactions as $row) {
        fputcsv($output, [
            $row['tracking_number'],
            $row['resident_name'],
            $row['cert_name'],
            $row['requested_at'],
            $row['status'],
            $row['payment_status'],
            $row['amount']
        ]);
    }
    fclose($output);
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="gov-page-heading no-print">
  <div>
    <div class="eyebrow">Official Reporting</div>
    <h1>Reports</h1>
    <p>Transactions report, certificate type breakdown, and printable monthly reports.</p>
  </div>
</div>

<div class="card no-print" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <div>
        <label class="form-label">Reporting Month</label>
        <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Generate</button>
      <button type="button" class="btn btn-success" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
      <button type="submit" name="export" value="csv" class="btn btn-secondary"><i class="fas fa-file-csv"></i> Export CSV</button>
    </form>
  </div>
</div>

<div id="printReport" class="official-report">
  <div class="report-letterhead">
    <div class="logo">
      <img src="<?= BASE_URL ?>/assets/images/pili_logo.png" alt="Barangay Logo" style="width: 72px; height: 72px; object-fit: contain;">
    </div>
    <div>
      <div class="rep">Republic of the Philippines</div>
      <div>Province of Cebu</div>
      <div>Municipality of Madridejos</div>
      <strong>BARANGAY PILI</strong>
      <h2>MONTHLY TRANSACTIONS REPORT</h2>
      <p><?= date('F 1, Y', strtotime($start)) ?> to <?= date('F t, Y', strtotime($start)) ?></p>
    </div>
    <div class="logo">
      <img src="<?= BASE_URL ?>/assets/images/municipality_logo.png" alt="Municipality Logo" style="width: 72px; height: 72px; object-fit: contain;">
    </div>
  </div>

  <div class="report-summary">
    <div><span>Total Transactions</span><strong><?= count($transactions) ?></strong></div>
    <div><span>Total Collections</span><strong>PHP <?= number_format($total_amount, 2) ?></strong></div>
    <div><span>Report Generated</span><strong><?= date('M d, Y') ?></strong></div>
  </div>

  <section class="report-section">
    <h3>Transactions Report</h3>
    <table class="report-table">
      <thead>
        <tr><th>Tracking No.</th><th>Resident</th><th>Certificate</th><th>Date Filed</th><th>Status</th><th>Payment</th><th>Amount</th></tr>
      </thead>
      <tbody>
        <?php if (!$transactions): ?>
        <tr><td colspan="7" class="text-center">No transactions for this period.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['tracking_number']) ?></td>
          <td><?= htmlspecialchars($row['resident_name']) ?></td>
          <td><?= htmlspecialchars($row['cert_name']) ?></td>
          <td><?= format_date($row['requested_at']) ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td><?= ucfirst($row['payment_status']) ?></td>
          <td>PHP <?= number_format((float)$row['amount'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="report-section">
    <h3>Breakdown by Certificate Types</h3>
    <table class="report-table">
      <thead>
        <tr><th>Certificate Type</th><th>Group</th><th>Total</th><th>Approved</th><th>Released</th><th>Collections</th></tr>
      </thead>
      <tbody>
        <?php if (!$by_cert): ?>
        <tr><td colspan="6" class="text-center">No certificate activity for this period.</td></tr>
        <?php endif; ?>
        <?php foreach ($by_cert as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['category'] ?? 'General') ?></td>
          <td><?= (int)$row['total'] ?></td>
          <td><?= (int)$row['approved'] ?></td>
          <td><?= (int)$row['released'] ?></td>
          <td>PHP <?= number_format((float)$row['collections'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="report-section printable-only">
    <h3>Printable Reports</h3>
    <div class="report-signatures">
      <div>
        <span>Prepared by:</span>
        <strong><?= htmlspecialchars($current['name'] ?: $current['username'] ?? 'Administrator') ?></strong>
        <small>Barangay System Administrator</small>
      </div>
      <div>
        <span>Certified Correct:</span>
        <strong>HON. JERRY S. CARANZO</strong>
        <small>Punong Barangay</small>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
