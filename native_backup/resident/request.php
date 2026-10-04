<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
if ($_SESSION['user_role'] !== 'resident') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit;
}

$page_title = 'New Request';
$active_nav = 'request';
$res_id     = (int)$_SESSION['resident_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cert_id = (int)($_POST['certificate_id'] ?? 0);
    $purpose = sanitize($_POST['purpose'] ?? '');

    if (!$cert_id || !$purpose) {
        set_flash('danger', 'Please complete all required fields.');
    } else {
        $cert = db_fetch_one("SELECT * FROM certificates WHERE id=? AND status='active'", 'i', $cert_id);
        if (!$cert) {
            set_flash('danger', 'Invalid certificate type.');
        } else {
            $tracking = generate_tracking_number();
            $req_id   = db_insert(
                "INSERT INTO requests (tracking_number, resident_id, certificate_id, purpose) VALUES (?,?,?,?)",
                'siis', $tracking, $res_id, $cert_id, $purpose
            );

            // Payment will be collected in person at the Barangay Office
            db_insert(
                "INSERT INTO payments (request_id, amount, payment_method, payment_status) VALUES (?,?,?,?)",
                'idss', $req_id, (float)$cert['fee'], 'cash', ($cert['fee'] == 0 ? 'waived' : 'unpaid')
            );

            log_activity('NEW_REQUEST', 'Requests', "Filed request $tracking");
            set_flash('success', "Request submitted! Your tracking number is: $tracking. Please proceed to Barangay Pili Office for payment.");
            header('Location: ' . BASE_URL . '/resident/my_requests.php'); exit;
        }
    }
}

$certs    = db_fetch_all("SELECT * FROM certificates WHERE status='active' ORDER BY name");
$resident = db_fetch_one("SELECT * FROM residents WHERE id=?", 'i', $res_id);

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:680px;margin:0 auto;">
  <div class="card">
    <div class="card-header">
      <h5><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:8px;"></i>New Clearance / Certificate Request</h5>
    </div>
    <div class="card-body">

      <!-- Resident Info (read-only) -->
      <div style="background:var(--gray-lighter);border-radius:10px;padding:16px;margin-bottom:24px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9ca3af;margin-bottom:8px;">Filing As</div>
        <div style="display:flex;align-items:center;gap:14px;">
          <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));
                      display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;">
            <?= strtoupper(substr($resident['first_name'],0,1)) ?>
          </div>
          <div>
            <div style="font-weight:700;font-size:15px;">
              <?= htmlspecialchars($resident['first_name'].' '.$resident['last_name']) ?>
            </div>
            <div style="font-size:12.5px;color:#6b7280;">
              <?= htmlspecialchars($resident['address']) ?>
              <?= $resident['purok'] ? ' · '.htmlspecialchars($resident['purok']) : '' ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Notice -->
      <div class="alert alert-info" style="margin-bottom:20px;border-left:4px solid #2563eb;">
        <i class="fas fa-university" style="margin-right:8px;"></i>
        <div>
          <strong>Payment Information</strong><br>
          <span>Payment will be processed at <strong>Barangay Pili Office</strong>.</span>
        </div>
      </div>

      <div class="alert alert-warning" style="margin-bottom:20px;border-left:4px solid #d97706;">
        <i class="fas fa-info-circle" style="margin-right:8px;"></i>
        <strong>Please proceed to Barangay Pili for payment and claiming of your requested document.</strong>
      </div>

      <form method="POST" id="requestForm">
        <!-- Certificate type cards -->
        <div class="form-group">
          <label class="form-label">Select Document to Request *</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px;margin-top:8px;">
            <?php foreach ($certs as $c): ?>
            <label style="cursor:pointer;">
              <input type="radio" name="certificate_id" value="<?= $c['id'] ?>"
                     class="cert-radio" style="display:none;" required
                     onchange="updateFee(<?= $c['fee'] ?>, '<?= htmlspecialchars($c['requirements']) ?>')">
              <div class="cert-card" id="cert-<?= $c['id'] ?>"
                   style="border:2px solid var(--gray-light);border-radius:10px;padding:14px;
                          transition:all .2s;text-align:center;">
                <div style="font-size:24px;margin-bottom:6px;">📄</div>
                <div style="font-size:13px;font-weight:700;color:var(--dark);margin-bottom:4px;">
                  <?= htmlspecialchars($c['name']) ?>
                </div>
                <div style="font-size:12px;color:var(--gray);">
                  <?= $c['fee'] > 0 ? '₱'.number_format($c['fee'],2) : 'FREE' ?>
                </div>
                <div style="font-size:11px;color:#9ca3af;margin-top:4px;">
                  ~<?= $c['processing_days'] ?> day<?= $c['processing_days']>1?'s':'' ?>
                </div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Requirements notice -->
        <div id="req-notice" style="display:none;margin:8px 0 16px;">
          <div class="alert alert-info" style="margin:0;">
            <i class="fas fa-clipboard-list"></i>
            <div>
              <strong>Requirements:</strong>
              <span id="req-text"></span>
            </div>
          </div>
        </div>

        <!-- Fee display -->
        <div id="fee-display" style="display:none;margin-bottom:16px;">
          <div class="alert alert-warning" style="margin:0;">
            <i class="fas fa-peso-sign"></i>
            <div>Processing fee: <strong id="fee-amount"></strong>. <em>Payment will be processed at Barangay Pili Office.</em></div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="purpose">Purpose / Reason for Request *</label>
          <textarea id="purpose" name="purpose" class="form-control" rows="3"
                    placeholder="e.g. For employment, scholarship, bank requirement…" required></textarea>
          <div class="form-text">State the exact purpose so the barangay can prepare the correct document.</div>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <a href="<?= BASE_URL ?>/resident/my_requests.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
          <button type="submit" class="btn btn-primary" style="flex:1;">
            <i class="fas fa-paper-plane"></i> Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updateFee(fee, reqs) {
  // Highlight selected card
  document.querySelectorAll('.cert-card').forEach(c => {
    c.style.borderColor = 'var(--gray-light)';
    c.style.background  = '';
  });
  const radio = document.querySelector('.cert-radio:checked');
  if (radio) {
    const card = radio.nextElementSibling;
    card.style.borderColor = 'var(--primary)';
    card.style.background  = 'var(--primary-light)';
  }
  // Show fee display
  const fd = document.getElementById('fee-display');
  const fa = document.getElementById('fee-amount');
  if (fee > 0) {
    fd.style.display = 'block';
    fa.textContent   = '₱' + parseFloat(fee).toFixed(2);
  } else {
    fd.style.display = 'none';
  }
  // Show requirements
  const rn = document.getElementById('req-notice');
  const rt = document.getElementById('req-text');
  if (reqs) {
    rn.style.display = 'block';
    rt.textContent   = ' ' + reqs;
  } else {
    rn.style.display = 'none';
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
