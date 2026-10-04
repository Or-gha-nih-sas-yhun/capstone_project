<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
require_role('staff');

$page_title = 'Manage Requests';
$active_nav = 'requests';

// --- Handle actions ---
$action = $_POST['action'] ?? '';
if ($action && isset($_POST['request_id'])) {
  $rid = (int) $_POST['request_id'];
  $remarks = sanitize($_POST['remarks'] ?? '');
  $uid = (int) $_SESSION['user_id'];

  if ($action === 'approve') {
    db_execute("UPDATE requests SET status='approved', approved_by=?, approved_at=NOW(), remarks=? WHERE id=?", 'isi', $uid, $remarks, $rid);
    // If paid via GCash, auto-mark payment as paid since proof is verified during approval
    db_execute("UPDATE payments SET payment_status='paid', paid_at=NOW(), received_by=? WHERE request_id=? AND payment_method='gcash'", 'ii', $uid, $rid);
    log_activity('APPROVE_REQUEST', 'Requests', "Approved request ID $rid");
    set_flash('success', 'Request approved successfully.');
  } elseif ($action === 'reject') {
    db_execute("UPDATE requests SET status='rejected', processed_by=?, processed_at=NOW(), remarks=? WHERE id=?", 'isi', $uid, $remarks, $rid);
    log_activity('REJECT_REQUEST', 'Requests', "Rejected request ID $rid");
    set_flash('danger', 'Request rejected.');
  } elseif ($action === 'process') {
    db_execute("UPDATE requests SET status='processing', processed_by=?, processed_at=NOW() WHERE id=?", 'ii', $uid, $rid);
    log_activity('PROCESS_REQUEST', 'Requests', "Processing request ID $rid");
    set_flash('success', 'Request marked as processing.');
  } elseif ($action === 'release') {
    db_execute("UPDATE requests SET status='released', released_at=NOW() WHERE id=?", 'i', $rid);
    // mark payment paid
    db_execute("UPDATE payments SET payment_status='paid', paid_at=NOW(), received_by=? WHERE request_id=?", 'ii', $uid, $rid);
    log_activity('RELEASE_REQUEST', 'Requests', "Released request ID $rid");
    set_flash('success', 'Request released and marked as paid.');
  } elseif ($action === 'archive') {
    db_execute("UPDATE requests SET archived_at=NOW(), archived_by=? WHERE id=?", 'ii', $uid, $rid);
    log_activity('ARCHIVE_REQUEST', 'Requests', "Archived request ID $rid");
    set_flash('success', 'Request archived safely. You can restore it from Archive.');
  }
  header('Location: ' . BASE_URL . '/admin/requests.php');
  exit;
}

// --- Filters & Pagination ---
$filter_status = sanitize($_GET['status'] ?? '');
$filter_search = sanitize($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 12;

$where = "WHERE r.archived_at IS NULL";
$params = [];
$types = '';

if ($filter_status) {
  $where .= " AND r.status=?";
  $params[] = $filter_status;
  $types .= 's';
}
if ($filter_search) {
  $like = "%$filter_search%";
  $where .= " AND (r.tracking_number LIKE ? OR CONCAT(res.first_name,' ',res.last_name) LIKE ? OR c.name LIKE ?)";
  $params = array_merge($params, [$like, $like, $like]);
  $types .= 'sss';
}

$total_sql = "SELECT COUNT(*) c FROM requests r JOIN residents res ON r.resident_id=res.id JOIN certificates c ON r.certificate_id=c.id $where";
$total_row = db_fetch_one($total_sql, $types, ...$params);
$total_count = (int) $total_row['c'];
$pg = paginate($total_count, $per_page, $page);

$offset_val = $pg['offset'];
$params_paged = array_merge($params, [$per_page, $offset_val]);
$types_paged = $types . 'ii';

$requests = db_fetch_all(
  "SELECT r.id, r.tracking_number, r.status, r.requested_at, r.purpose,
            CONCAT(res.first_name,' ',res.last_name) AS resident_name,
            res.contact_number, c.name AS cert_name, c.fee,
            p.payment_status
     FROM requests r
     JOIN residents res ON r.resident_id=res.id
     JOIN certificates c ON r.certificate_id=c.id
     LEFT JOIN payments p ON p.request_id=r.id
     $where ORDER BY r.requested_at DESC LIMIT ? OFFSET ?",
  $types_paged,
  ...$params_paged
);

// View single request
$view_req = null;
if (isset($_GET['view'])) {
  $view_req = db_fetch_one(
    "SELECT r.*, CONCAT(res.first_name,' ',res.middle_name,' ',res.last_name) AS resident_name,
                res.address, res.contact_number, res.email,
                c.name AS cert_name, c.fee, c.requirements,
                p.payment_status, p.amount AS paid_amount, p.receipt_number, p.proof_of_payment, p.payment_method,
                CONCAT(pu.username) AS processed_by_name,
                CONCAT(au.username) AS approved_by_name
         FROM requests r
         JOIN residents res ON r.resident_id=res.id
         JOIN certificates c ON r.certificate_id=c.id
         LEFT JOIN payments p ON p.request_id=r.id
         LEFT JOIN users pu ON r.processed_by=pu.id
         LEFT JOIN users au ON r.approved_by=au.id
         WHERE r.tracking_number=?",
    's',
    sanitize($_GET['view'])
  );
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filters -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:16px 20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" placeholder="Tracking #, name, document…"
          value="<?= htmlspecialchars($filter_search) ?>">
      </div>
      <div style="min-width:160px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <?php foreach (['pending', 'processing', 'approved', 'rejected', 'released'] as $s): ?>
            <option value="<?= $s ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
      <a href="<?= BASE_URL ?>/admin/requests.php" class="btn btn-secondary">Reset</a>
    </form>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>
      All Requests <span style="font-size:13px;font-weight:400;color:#6b7280;">(<?= $total_count ?>)</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Tracking #</th>
          <th>Resident</th>
          <th>Document</th>
          <th>Fee</th>
          <th>Payment</th>
          <th>Date Filed</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted" style="padding:40px;">
              <?= $filter_status ? 'No ' . ucfirst($filter_status) . ' Requests Found.' : 'No requests found.' ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr>
              <td><code style="font-size:11px;"><?= htmlspecialchars($r['tracking_number']) ?></code></td>
              <td><span style="font-weight:600;"><?= htmlspecialchars($r['resident_name']) ?></span>
                <br><small class="text-muted"><?= htmlspecialchars($r['contact_number']) ?></small>
              </td>
              <td><?= htmlspecialchars($r['cert_name']) ?></td>
              <td>₱<?= number_format($r['fee'], 2) ?></td>
              <td><?= status_badge($r['payment_status'] ?? 'unpaid') ?></td>
              <td><small><?= format_datetime($r['requested_at']) ?></small></td>
              <td><?= status_badge($r['status']) ?></td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                  <a href="?view=<?= $r['tracking_number'] ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if ($r['status'] === 'approved' || $r['status'] === 'released'): ?>
                    <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $r['id'] ?>&preview=1" target="_blank"
                      class="btn btn-outline-primary btn-sm" title="Preview Certificate">
                      <i class="fas fa-up-right-from-square"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $r['id'] ?>&print=1" target="_blank"
                      class="btn btn-success btn-sm" title="Print Certificate">
                      <i class="fas fa-print"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <!-- Pagination -->
  <?php if ($pg['total_pages'] > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      <div class="pagination">
        <?php for ($i = 1; $i <= $pg['total_pages']; $i++): ?>
          <a href="?page=<?= $i ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>"
            class="page-btn <?= $i === $pg['current'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- View Modal -->
<?php if ($view_req): ?>
  <div class="modal-overlay show" id="viewModal">
    <div class="modal-box" style="max-width:660px;">
      <div class="modal-header">
        <h5><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>Request Details</h5>
        <button class="modal-close" onclick="window.location='<?= BASE_URL ?>/admin/requests.php'">&times;</button>
      </div>
      <div class="modal-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Tracking #</div>
            <div style="font-weight:700;font-family:monospace;"><?= htmlspecialchars($view_req['tracking_number']) ?>
            </div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Status</div>
            <div><?= status_badge($view_req['status']) ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Resident</div>
            <div style="font-weight:600;"><?= htmlspecialchars($view_req['resident_name']) ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Contact</div>
            <div><?= htmlspecialchars($view_req['contact_number']) ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Document</div>
            <div style="font-weight:600;"><?= htmlspecialchars($view_req['cert_name']) ?></div>
          </div>
          <div>
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Fee & Payment</div>
            <div style="font-weight:700;color:var(--primary);display:flex;align-items:center;gap:6px;">
              ₱<?= number_format($view_req['fee'], 2) ?>
              <?= status_badge($view_req['payment_status'] ?? 'unpaid') ?>
              <?php if ($view_req['payment_method'] === 'gcash'): ?>
                <span class="badge bg-info" style="font-size:10px;text-transform:uppercase;">GCash</span>
              <?php endif; ?>
            </div>
          </div>
          <div style="grid-column:span 2;">
            <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Purpose</div>
            <div><?= htmlspecialchars($view_req['purpose']) ?></div>
          </div>
          <?php if ($view_req['remarks']): ?>
            <div style="grid-column:span 2;">
              <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;">Remarks</div>
              <div><?= htmlspecialchars($view_req['remarks']) ?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($view_req['proof_of_payment'])): ?>
            <div style="grid-column:span 2;margin-top:10px;">
              <div style="font-size:11px;color:#9ca3af;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Proof of
                Payment (GCash Receipt)</div>
              <?php
              $proof_ext = strtolower(pathinfo($view_req['proof_of_payment'], PATHINFO_EXTENSION));
              $proof_url = BASE_URL . '/assets/uploads/' . htmlspecialchars($view_req['proof_of_payment']);
              if (in_array($proof_ext, ['jpg', 'jpeg', 'png', 'gif'])):
                ?>
                <a href="<?= $proof_url ?>" target="_blank" title="Click to view full size">
                  <img src="<?= $proof_url ?>" alt="Proof of Payment"
                    style="max-width:100%;max-height:280px;border-radius:8px;border:1.5px solid var(--gray-light);box-shadow:var(--shadow);display:block;">
                </a>
              <?php else: ?>
                <a href="<?= $proof_url ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                  <i class="fas fa-file-pdf"></i> View Proof Document (PDF)
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Action buttons -->
        <?php if (!in_array($view_req['status'], ['rejected', 'released'])): ?>
          <hr style="margin-bottom:16px;">
          <form method="POST" id="actionForm">
            <input type="hidden" name="request_id" value="<?= $view_req['id'] ?>">
            <div class="form-group">
              <label class="form-label">Remarks / Notes</label>
              <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks…"></textarea>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <?php if ($view_req['status'] === 'pending'): ?>
                <button type="submit" name="action" value="process" class="btn btn-warning">
                  <i class="fas fa-cog"></i> Mark Processing
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger"
                  onclick="return confirm('Reject this request?')">
                  <i class="fas fa-times"></i> Reject
                </button>
              <?php elseif ($view_req['status'] === 'processing'): ?>
                <button type="submit" name="action" value="approve" class="btn btn-success">
                  <i class="fas fa-check"></i> Approve
                </button>
                <button type="submit" name="action" value="reject" class="btn btn-danger"
                  onclick="return confirm('Reject this request?')">
                  <i class="fas fa-times"></i> Reject
                </button>
              <?php elseif ($view_req['status'] === 'approved'): ?>
                <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $view_req['id'] ?>&preview=1" target="_blank"
                  class="btn btn-outline-primary">
                  <i class="fas fa-up-right-from-square"></i> Preview Certificate
                </a>
                <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $view_req['id'] ?>&print=1" target="_blank"
                  class="btn btn-primary">
                  <i class="fas fa-print"></i> Print Document
                </a>
                <button type="submit" name="action" value="release" class="btn btn-success">
                  <i class="fas fa-box-open"></i> Mark Released
                </button>
              <?php endif; ?>
              <button type="submit" name="action" value="archive" class="btn btn-secondary"
                onclick="return confirm('Archive this request?')">
                <i class="fas fa-box-archive"></i> Archive
              </button>
            </div>
          </form>
        <?php else: ?>
          <?php if (in_array($view_req['status'], ['approved', 'released'])): ?>
            <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $view_req['id'] ?>" target="_blank"
              class="btn btn-outline-primary">
              <i class="fas fa-up-right-from-square"></i> Preview Certificate
            </a>
            <a href="<?= BASE_URL ?>/templates/certificate.php?id=<?= $view_req['id'] ?>&print=1" target="_blank"
              class="btn btn-primary">
              <i class="fas fa-print"></i> Print Document
            </a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>