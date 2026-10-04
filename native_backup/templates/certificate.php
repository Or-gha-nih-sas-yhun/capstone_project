<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Printing is only accessible from the Admin Panel
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'resident') {
  header('Location: ' . BASE_URL . '/resident/my_requests.php');
  exit;
}


$req_id = (int) ($_GET['id'] ?? 0);
if (!$req_id) {
  die('Invalid request.');
}

$template_row = db_fetch_one(
  "SELECT c.name, c.template_file FROM requests r JOIN certificates c ON r.certificate_id=c.id WHERE r.id=?",
  'i',
  $req_id
);
if ($template_row) {
  $template = $template_row['template_file'] ?: certificate_template_for($template_row['name']);
  $allowed_templates = ['certificate_clearance.php', 'certificate_indigency.php', 'certificate_residency.php', 'certificate_moral.php'];
  if (in_array($template, $allowed_templates, true)) {
    require __DIR__ . '/' . $template;
    exit;
  }
}

$req = db_fetch_one(
  "SELECT r.*, res.first_name, res.middle_name, res.last_name, res.suffix,
            res.address, res.birthdate, res.civil_status, res.gender,
            res.contact_number, res.years_of_residency, res.purok,
            c.name AS cert_name, c.fee,
            p.payment_status, p.receipt_number,
            CONCAT(u.username) AS issued_by
     FROM requests r
     JOIN residents res ON r.resident_id = res.id
     JOIN certificates c   ON r.certificate_id = c.id
     LEFT JOIN payments p  ON p.request_id = r.id
     LEFT JOIN users u     ON r.approved_by = u.id
     WHERE r.id = ? AND r.status IN ('approved','released')",
  'i',
  $req_id
);

if (!$req) {
  die('Document not available. Request must be approved first.');
}

// Access control: residents can only print their own
if ($_SESSION['user_role'] === 'resident' && (int) $_SESSION['resident_id'] !== (int) $req['resident_id']) {
  die('Unauthorized.');
}

$captain = db_fetch_one("SELECT name FROM officials WHERE position LIKE '%Captain%' AND status='active' LIMIT 1");
$officials = db_fetch_all("SELECT * FROM officials WHERE status='active' ORDER BY sort_order");

$full_name = strtoupper(trim(
  $req['first_name'] . ' ' . $req['middle_name'] . ' ' . $req['last_name'] . ($req['suffix'] ? ' ' . $req['suffix'] : '')
));
$issued_date = date('F d, Y');
$doc_number = 'DOC-' . date('Y') . '-' . str_pad($req['id'], 5, '0', STR_PAD_LEFT);
$age = calculate_age($req['birthdate']);
$auto_print = isset($_GET['print']);

// Purpose text per cert type
$cert = strtolower($req['cert_name']);
if (str_contains($cert, 'clearance')) {
  $body_text = "This is to certify that <strong>$full_name</strong>, {$age} years of age, {$req['civil_status']}, 
    a resident of {$req['address']}, Barangay Pili, is known to be a person of <strong>GOOD MORAL CHARACTER</strong> 
    and has no derogatory record on file in this barangay. This clearance is issued upon the request of the above-named 
    person for the purpose of <strong>" . strtoupper($req['purpose']) . "</strong>.";
} elseif (str_contains($cert, 'indigency')) {
  $body_text = "This is to certify that <strong>$full_name</strong>, {$age} years of age, {$req['civil_status']}, 
    a resident of {$req['address']}, Barangay Pili, belongs to the <strong>INDIGENT SECTOR</strong> of our community 
    and is financially incapable. This certification is issued upon request for the purpose of 
    <strong>" . strtoupper($req['purpose']) . "</strong>.";
} elseif (str_contains($cert, 'residency')) {
  $body_text = "This is to certify that <strong>$full_name</strong>, {$age} years of age, {$req['civil_status']}, 
    is a <strong>BONA FIDE RESIDENT</strong> of {$req['address']}, Barangay Pili, for 
    {$req['years_of_residency']} year(s). This certification is issued upon request for the purpose of 
    <strong>" . strtoupper($req['purpose']) . "</strong>.";
} elseif (str_contains($cert, 'business')) {
  $body_text = "This is to certify that <strong>$full_name</strong>, a resident of {$req['address']}, Barangay Pili, 
    has been granted <strong>BARANGAY BUSINESS CLEARANCE</strong> and has no pending case or complaint in this barangay. 
    This clearance is issued for the purpose of <strong>" . strtoupper($req['purpose']) . "</strong>.";
} elseif (str_contains($cert, 'jobseeker')) {
  $body_text = "This is to certify that <strong>$full_name</strong>, {$age} years of age, a resident of 
    {$req['address']}, Barangay Pili, is a <strong>FIRST TIME JOBSEEKER</strong> as defined under 
    Republic Act No. 11261. This certification is issued for the purpose of 
    <strong>" . strtoupper($req['purpose']) . "</strong>.";
} else {
  $body_text = "This is to certify that <strong>$full_name</strong>, {$age} years of age, {$req['civil_status']}, 
    a resident of {$req['address']}, Barangay Pili, is hereby issued this <strong>{$req['cert_name']}</strong> 
    upon request for the purpose of <strong>" . strtoupper($req['purpose']) . "</strong>.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($req['cert_name']) ?> — <?= $full_name ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Inter:wght@400;600;700;800&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #f0f0f0;
    }

    .page {
      width: 215.9mm;
      min-height: 279.4mm;
      background: #fff;
      margin: 20px auto;
      padding: 15mm 15mm;
      position: relative;
      display: flex;
      gap: 12px;
    }

    .no-print {
      text-align: center;
      padding: 16px;
      background: #1a56db;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .no-print button {
      background: rgba(255, 255, 255, .2);
      border: 1px solid rgba(255, 255, 255, .4);
      color: #fff;
      padding: 8px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
    }

    .no-print a {
      color: #fff;
      font-size: 13px;
      opacity: .8;
    }

    /* Left Sidebar */
    .sidebar {
      width: 90px;
      background: #f5e6d3;
      border: 2px solid #8b7355;
      padding: 12px 8px;
      position: relative;
    }

    .sidebar h3 {
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      text-align: center;
      margin-bottom: 10px;
      color: #5d4037;
      letter-spacing: 0.5px;
    }

    .officials-list {
      font-size: 7.5px;
      line-height: 1.3;
      color: #5d4037;
    }

    .official-group {
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(93, 64, 55, 0.3);
    }

    .official-group:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .official-group strong {
      display: block;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    .official-name {
      font-size: 7px;
      font-weight: 600;
    }

    /* Main Content */
    .content {
      flex: 1;
    }

    /* Header */
    .doc-header {
      text-align: center;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
      margin-bottom: 12px;
      position: relative;
    }

    .header-seals {
      display: flex;
      justify-content: center;
      gap: 60px;
      margin-bottom: 8px;
      align-items: center;
    }

    .header-seals img {
      width: 50px;
      height: 50px;
      object-fit: contain;
    }

    .doc-header .republic {
      font-size: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0 0 1px 0;
    }

    .doc-header .province {
      font-size: 8.5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0;
    }

    .doc-header .municipality {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #444;
      margin: 0;
    }

    .doc-header .brgy-name {
      font-size: 18px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #333;
      margin: 2px 0;
    }

    /* Office title */
    .office-title {
      text-align: center;
      font-size: 13px;
      font-weight: 700;
      text-transform: capitalize;
      font-family: 'Lora', serif;
      color: #333;
      margin: 8px 0;
      border-bottom: 2px solid #333;
      padding-bottom: 6px;
    }

    /* Certificate Title */
    .cert-title {
      text-align: center;
      margin: 10px 0;
    }

    .cert-title h1 {
      font-size: 14px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #333;
    }

    /* Body */
    .doc-body {
      font-size: 10.5px;
      line-height: 1.6;
      text-align: justify;
      margin-bottom: 12px;
    }

    .doc-body p {
      margin-bottom: 6px;
    }

    /* Date and signatures */
    .cert-footer {
      margin-top: 14px;
      font-size: 10px;
    }

    .issued-date {
      margin-bottom: 10px;
      line-height: 1.4;
    }

    .issued-date strong {
      font-weight: 700;
    }

    .sig-section {
      display: flex;
      justify-content: center;
      gap: 40px;
      margin-top: 16px;
    }

    .sig-block {
      text-align: center;
      width: 100px;
    }

    .sig-line {
      border-top: 1px solid #333;
      height: 30px;
      margin-bottom: 2px;
    }

    .sig-name {
      font-size: 8.5px;
      font-weight: 700;
      text-transform: uppercase;
    }

    .sig-pos {
      font-size: 8px;
      color: #666;
    }

    /* Footer stamp */
    .doc-stamp {
      text-align: center;
      font-size: 8px;
      color: #888;
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px solid #ddd;
    }

    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      right: 5%;
      transform: translateY(-50%) rotate(-25deg);
      font-size: 60px;
      font-weight: 900;
      color: rgba(0, 0, 0, .03);
      white-space: nowrap;
      pointer-events: none;
      z-index: 0;
    }

    @media print {
      body {
        background: #fff;
      }

      .no-print {
        display: none !important;
      }

      .page {
        margin: 0;
        box-shadow: none;
      }
    }
  </style>
</head>

<body <?= $auto_print ? 'onload="window.print()"' : '' ?>>

  <!-- Print toolbar (Admin/Staff only) -->
  <div class="no-print">
    <span><i class="fas fa-file-alt"></i> <?= htmlspecialchars($req['cert_name']) ?> — <?= $full_name ?></span>
    <button onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    <button onclick="window.close()">Close</button>
  </div>

  <!-- Document -->
  <div class="page">
    <!-- Header -->
    <div class="doc-header">
      <div class="header-seals">
        <img src="<?= BASE_URL ?>/assets/images/municipality_logo.png" alt="Barangay Pili Seal"
          style="width: 50px; height: 50px;">
        <img src="<?= BASE_URL ?>/assets/images/municipality_logo.png" alt="Municipality Seal"
          style="width: 50px; height: 50px;">
      </div>
      <div class="republic">Republic of the Philippines</div>
      <div class="province">Province of Cebu</div>
      <div class="municipality">Municipality of Madridejos</div>
      <div class="brgy-name">BARANGAY PILI</div>
      <div style="font-size: 8px; color: #1a56db; margin-top: 2px;">Email: brgy.pili.mad@gmail.com</div>
    </div>

    <!-- Office Title -->
    <div class="office-title">Office of the Barangay Captain</div>

    <!-- Left Sidebar with Officials -->
    <div class="sidebar">
      <h3>SANGGUNIANG<br>BARANGAY</h3>
      <div class="officials-list">
        <div class="official-group">
          <strong>HON. JERRY CARANZO</strong>
          <div class="official-name">Punong barangay</div>
        </div>

        <div class="official-group">
          <strong>KAGAWAD:</strong>
          <div class="official-name">HON. SOHO GIDO</div>
          <div class="official-name">HON. JIMMY CAHUTAY</div>
          <div class="official-name">HON. BERNARDO OPLAS</div>
          <div class="official-name">HON. GEMMA GILIBUELA</div>
          <div class="official-name">HON. ERWIN CORRIDOR</div>
          <div class="official-name">HON. CRISTINA CARANZO</div>
          <div class="official-name">HON. MARIA LEZEL HYER</div>
        </div>

        <div class="official-group">
          <strong>SK CHAIRMAN:</strong>
          <div class="official-name">HON. RITCHIE SINDAY</div>
        </div>

        <div class="official-group">
          <strong>SECRETARY:</strong>
          <div class="official-name">RANDY B. DESPI</div>
        </div>

        <div class="official-group">
          <strong>TREASURER:</strong>
          <div class="official-name">MARILYN IUSTRISIMO</div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="content">
      <div class="watermark">BARANGAY PILI</div>


      <!-- Certificate Title -->
      <div class="cert-title">
        <h1>BARANGAY CERTIFICATION</h1>
        <div style="font-size: 9px; margin-top: 2px; font-style: italic;"><?php
        // Show different subtitles based on cert type
        $cert = strtolower($req['cert_name']);
        if (str_contains($cert, 'jobseeker')) {
          echo '(First Time Jobseekers Assistance Act- RA 11261)';
        } elseif (str_contains($cert, 'indigency')) {
          echo '(Certificate of Indigency)';
        } else {
          echo '(' . htmlspecialchars($req['cert_name']) . ')';
        }
        ?></div>
      </div>

      <!-- Body -->
      <div class="doc-body">
        <p><strong>To whom it may concern:</strong></p>

        <?php
        $cert = strtolower($req['cert_name']);
        if (str_contains($cert, 'jobseeker')) {
          echo '<p>This is to certify that <strong>' . htmlspecialchars($full_name) . '</strong>, ' . $age . ' years old, a resident of Purok <strong>' . htmlspecialchars($req['purok'] ?? '') . '</strong>, Barangay Pili, Madridejos, Cebu, is a qualified first time jobseeker under <strong>Republic Act No. 11261</strong>.</p>';
          echo '<p>The holder was informed of the rights, duties, and responsibilities under the First Time Jobseekers Assistance Act and has executed the required undertaking before this barangay office.</p>';
          echo '<p>This certification is issued upon request for <strong>' . htmlspecialchars(strtoupper($req['purpose'])) . '</strong> and for lawful purposes it may serve.</p>';
        } else {
          echo '<p>' . $body_text . '</p>';
        }
        ?>
      </div>

      <!-- Date and Signatures -->
      <div class="cert-footer">
        <div style="margin-top: 12px; font-size: 9px; line-height: 1.6;">
          <p>Signed this <strong><?= date('jS') ?> day of <?= date('F Y') ?></strong>, in the Madridejos, Cebu</p>
        </div>

        <div style="margin-top: 20px; text-align: center;">
          <div style="border: 1px solid #333; height: 40px; width: 120px; margin: 0 auto 6px;"></div>
          <p style="font-size: 9px; font-weight: 600;">First Time Jobseeker</p>
          <div style="margin-top: 12px; border-top: 1px solid #333; padding-top: 2px;">
            <p style="font-size: 9px;"><strong><?= date('M d, Y') ?></strong></p>
            <p style="font-size: 8px;">Date</p>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 16px; font-size: 9px;">
          <div>
            <p style="margin-bottom: 14px; font-size: 8.5px;"><strong>Witnessed by:</strong></p>
            <div style="border-top: 1px solid #333; height: 28px; margin-bottom: 2px;"></div>
            <p style="font-size: 8px;">Kagawad</p>
            <p style="font-size: 8px; margin-top: 8px; border-top: 1px solid #333; padding-top: 2px;"></p>
            <p style="font-size: 8px;">Date</p>
          </div>
          <div></div>
        </div>

        <div
          style="margin-top: 16px; font-size: 8px; text-align: center; padding-top: 8px; border-top: 1px solid #ddd;">
          <p><strong>This certification is valid only until <?= date('M d, Y', strtotime('+1 year')) ?> one (1) year
              from the issuance.</strong></p>
        </div>

        <div class="doc-stamp" style="margin-top: 12px; font-size: 8px; border: none; padding: 0;">
          NOT VALID WITHOUT OFFICIAL DRY SEAL
        </div>
      </div>
    </div>
  </div>

</body>

</html>