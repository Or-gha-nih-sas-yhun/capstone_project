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

$req = db_fetch_one(
  "SELECT r.*,
            res.first_name,
            res.middle_name,
            res.last_name,
            res.suffix,
            res.address,
            res.birthdate,
            res.civil_status,
            res.gender,
            res.contact_number,
            res.years_of_residency,
            res.purok,
            c.name AS cert_name,
            c.fee,
            p.payment_status,
            p.receipt_number,
            CONCAT(u.username) AS issued_by
     FROM requests r
     JOIN residents res ON r.resident_id = res.id
     JOIN certificates c ON r.certificate_id = c.id
     LEFT JOIN payments p ON p.request_id = r.id
     LEFT JOIN users u ON r.approved_by = u.id
     WHERE r.id = ?
     AND r.status IN ('approved','released')",
  'i',
  $req_id
);

if (!$req) {
  die('Document not available.');
}

if (
  $_SESSION['user_role'] === 'resident' &&
  (int) $_SESSION['resident_id'] !== (int) $req['resident_id']
) {
  die('Unauthorized.');
}

$full_name = strtoupper(
  trim(
    $req['first_name'] . ' ' .
    $req['middle_name'] . ' ' .
    $req['last_name'] .
    ($req['suffix'] ? ' ' . $req['suffix'] : '')
  )
);

$auto_print = isset($_GET['print']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">

  <title>
    CERTIFICATE OF GOOD MORAL
  </title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: #dcdcdc;
      font-family: Georgia, serif;
    }

    .toolbar {
      background: #b91c1c;
      color: #fff;
      padding: 15px;
      text-align: center;
    }

    .toolbar button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      margin-left: 10px;
      cursor: pointer;
      background: #fff;
    }

    .page {
      width: 210mm;
      min-height: 297mm;
      background: #fff;
      margin: 20px auto;
      padding: 20mm;
      position: relative;
      overflow: hidden;
    }

    /* HEADER */

    .top-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo img {
      width: 90px;
      height: 90px;
      object-fit: contain;
    }

    .header-text {
      flex: 1;
      text-align: center;
    }

    .republic,
    .province,
    .municipality {
      font-size: 18px;
      line-height: 1.3;
    }

    .barangay {
      font-size: 34px;
      font-weight: 800;
      margin-top: 4px;
    }

    .office-title {
      text-align: center;
      font-size: 30px;
      font-weight: bold;
      margin-top: 10px;
    }

    .header-line {
      border-top: 3px solid #333;
      margin-top: 10px;
      margin-bottom: 25px;
    }

    /* MAIN */

    .main-content {
      display: flex;
      gap: 25px;
    }

    /* SIDEBAR */

    .sidebar {
      width: 180px;
      background: #efdecd;
      border-radius: 18px;
      padding: 25px 15px;
      min-height: 850px;
      position: relative;
      text-align: center;
      flex-shrink: 0;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: -18px;
      left: -10px;
      width: 200px;
      height: 35px;
      background: #ead7c6;
      border-radius: 30px;
    }

    .sidebar::after {
      content: '';
      position: absolute;
      bottom: -18px;
      left: -10px;
      width: 200px;
      height: 35px;
      background: #ead7c6;
      border-radius: 30px;
    }

    .sidebar h3 {
      font-size: 18px;
      margin-bottom: 25px;
      color: #5a4b3e;
    }

    .official-group {
      margin-bottom: 20px;
    }

    .official-group strong {
      display: block;
      font-size: 14px;
      color: #5a4b3e;
    }

    .official-name {
      font-size: 13px;
      line-height: 1.4;
      color: #5a4b3e;
    }

    /* CONTENT */

    .content {
      flex: 1;
      position: relative;
      z-index: 2;
    }

    .cert-title {
      text-align: center;
      margin-bottom: 35px;
    }

    .cert-title h1 {
      font-size: 30px;
      font-style: italic;
      color: #404854;
    }

    .doc-body {
      font-size: 22px;
      line-height: 1.9;
      color: #444;
      text-align: justify;
    }

    .doc-body p {
      margin-bottom: 24px;
      text-indent: 50px;
    }

    .doc-body p:first-child {
      text-indent: 0;
    }

    /* SIGNATURE */

    .sig-section {
      display: flex;
      justify-content: flex-end;
      margin-top: 70px;
    }

    .sig-block {
      width: 45%;
      text-align: center;
    }

    .signature-line {
      border-top: 2px solid #333;
      margin-top: 40px;
      margin-bottom: 8px;
    }

    /* FOOTER */

    .footer-info {
      margin-top: 50px;
      font-size: 16px;
      line-height: 1.7;
    }

    .doc-stamp {
      margin-top: 35px;
      text-align: center;
      font-size: 15px;
      font-weight: bold;
    }

    /* WATERMARK */

    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-25deg);
      font-size: 140px;
      font-weight: 900;
      color: rgba(0, 0, 0, 0.04);
      z-index: 0;
      white-space: nowrap;
    }

    @media print {

      body {
        background: #fff;
      }

      .toolbar {
        display: none;
      }

      .page {
        margin: 0;
      }

    }
  </style>

</head>

<body <?= $auto_print ? 'onload="window.print()"' : '' ?>>

  <div class="toolbar">

    CERTIFICATE OF GOOD MORAL

    <button onclick="window.print()">
      <i class="fas fa-print"></i> Print
    </button>
    <button onclick="window.close(); setTimeout(function(){ window.history.back(); }, 100);" style="background:#fee2e2; color:#b91c1c;">Close</button>

  </div>

  <div class="page">

    <div class="watermark">
      BARANGAY PILI
    </div>

    <!-- HEADER -->

    <div class="top-header">

      <div class="logo">
        <img src="<?= BASE_URL ?>/assets/images/barangay_logo.png">
      </div>

      <div class="header-text">

        <div class="republic">
          Republic of the Philippines
        </div>

        <div class="province">
          Province of Cebu
        </div>

        <div class="municipality">
          Municipality of Madridejos
        </div>

        <div class="barangay">
          BARANGAY PILI
        </div>

      </div>

      <div class="logo">
        <img src="<?= BASE_URL ?>/assets/images/municipality_logo.png">
      </div>

    </div>

    <div class="office-title">
      Office of the Punong Barangay
    </div>

    <div class="header-line"></div>

    <!-- MAIN CONTENT -->

    <div class="main-content">

      <!-- SIDEBAR -->

      <div class="sidebar">

        <h3>
          SANGGUNIANG<br>BARANGAY
        </h3>

        <div class="official-group">
          <strong>HON. JERRY CARANZO</strong>
          <div class="official-name">
            Punong Barangay
          </div>
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
          <div class="official-name">
            HON. RITCHIE SINDAY
          </div>
        </div>

        <div class="official-group">
          <strong>SECRETARY:</strong>
          <div class="official-name">
            RANDY B. DESPI
          </div>
        </div>

        <div class="official-group">
          <strong>TREASURER:</strong>
          <div class="official-name">
            MARILYN IUSTRISIMO
          </div>
        </div>

      </div>

      <!-- CONTENT -->

      <div class="content">

        <div class="cert-title">
          <h1>CERTIFICATE OF GOOD MORAL</h1>
        </div>

        <div class="doc-body">

          <p>
            <strong>To whom it may concern:</strong>
          </p>

          <p>
            This is to certify that
            <strong><?= htmlspecialchars($full_name) ?></strong>,
            a resident of
            <?= htmlspecialchars($req['address']) ?>,
            Barangay Pili, Madridejos, Cebu,
            is personally known to this office
            as a person of good moral character,
            law-abiding, peace-loving,
            and socially responsible citizen
            of the community.
          </p>

          <p>
            Records available in this barangay
            further show that the above-named person
            has no derogatory or criminal record
            filed in this office as of this date.
          </p>

          <p>
            This certification is issued upon
            the request of the above-named person
            for
            <strong>
              <?= htmlspecialchars(strtoupper($req['purpose'])) ?>
            </strong>
            and for whatever lawful purpose
            it may serve.
          </p>

          <p>
            <strong>
              Issued this <?= date('jS') ?>
              day of <?= date('F Y') ?>,
              at Pili, Madridejos, Cebu.
            </strong>
          </p>

          <!-- SIGNATURE -->

          <div class="sig-section">

            <div class="sig-block">

              <div class="signature-line"></div>

              <strong>
                HON. JERRY S. CARANZO
              </strong><br>

              Punong Barangay

            </div>

          </div>

          <!-- FOOTER -->

          <div class="footer-info">

            <div>
              Paid under O.R. No.
              <?= htmlspecialchars($req['receipt_number'] ?? 'N/A') ?>
            </div>

            <div>
              Certification Fee:
              PHP <?= number_format((float) $req['fee'], 2) ?>
            </div>

            <div>
              Documentary Stamp:
              As applicable
            </div>

            <div>
              Amount Paid:
              PHP <?= number_format((float) $req['fee'], 2) ?>
            </div>

            <br>

            <div>
              Issued on <?= date('m/d/Y') ?>
            </div>

            <div>
              Issued at Pili, Madridejos, Cebu
            </div>

          </div>

          <div class="doc-stamp">

            VALID ONLY UNTIL DECEMBER 31,
            <?= date('Y') ?>

            AND NOT VALID WITHOUT OFFICIAL DRY SEAL

          </div>

        </div>

      </div>

    </div>

  </div>

</body>

</html>