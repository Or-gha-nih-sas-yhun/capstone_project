<?php
// ============================================================
// Barangay Pili — Browser-Based Database Installer
// Visit: http://localhost/Brgy.pili_clearance/install.php
// DELETE this file after installation!
// ============================================================
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'brgy_pili_clearance');
define('DB_CHARSET', 'utf8mb4');

$step    = $_POST['step'] ?? 'check';
$results = [];
$success = true;

$sql_file = __DIR__ . '/database/brgy_pili.sql';

function run_sql(mysqli $conn, string $sql_path): array {
    $logs = [];
    $sql  = file_get_contents($sql_path);
    // Split on semicolons (skip empty)
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $q) {
        if (empty($q) || substr($q, 0, 2) === '--') continue;
        if ($conn->query($q) === false) {
            $logs[] = ['ok' => false, 'msg' => substr($q, 0, 80) . '… → ' . $conn->error];
        } else {
            $logs[] = ['ok' => true,  'msg' => substr($q, 0, 80) . '…'];
        }
    }
    return $logs;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'install') {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        $success = false;
        $results[] = ['ok' => false, 'msg' => 'Cannot connect to MySQL: ' . $conn->connect_error];
    } else {
        $conn->set_charset(DB_CHARSET);
        $results = run_sql($conn, $sql_file);
        foreach ($results as $r) { if (!$r['ok']) { $success = false; } }
        $conn->close();
    }
    $step = 'result';
}

// Pre-check
$checks = [];
$checks[] = ['label' => 'PHP Version ≥ 7.4',  'ok' => version_compare(PHP_VERSION,'7.4','>='), 'val' => PHP_VERSION];
$checks[] = ['label' => 'MySQLi Extension',     'ok' => extension_loaded('mysqli'),              'val' => extension_loaded('mysqli')?'Loaded':'MISSING'];
$checks[] = ['label' => 'SQL File Exists',      'ok' => file_exists($sql_file),                  'val' => $sql_file];
$checks[] = ['label' => 'Uploads Dir Writable', 'ok' => is_writable(__DIR__.'/assets/uploads/'), 'val' => __DIR__.'/assets/uploads/'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — Barangay Pili System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#111827,#1f2937);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
  .box{background:#fff;border-radius:16px;box-shadow:0 20px 50px rgba(0,0,0,.4);width:100%;max-width:640px;overflow:hidden;}
  .box-head{background:linear-gradient(135deg,#1a56db,#1e429f);padding:28px 32px;color:#fff;text-align:center;}
  .box-head h1{font-size:22px;font-weight:800;margin-bottom:4px;}
  .box-head p{font-size:13px;opacity:.8;}
  .box-body{padding:28px 32px;}
  .check-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:8px;margin-bottom:8px;font-size:13.5px;}
  .check-row.ok{background:#f0fdf4;color:#166534;}
  .check-row.fail{background:#fef2f2;color:#991b1b;}
  .check-val{margin-left:auto;font-size:11px;opacity:.7;font-family:monospace;}
  .log-row{display:flex;gap:10px;padding:7px 10px;border-radius:6px;font-size:12.5px;margin-bottom:4px;}
  .log-row.ok{background:#f0fdf4;color:#166534;}
  .log-row.fail{background:#fef2f2;color:#991b1b;}
  .log-row code{font-family:monospace;word-break:break-all;}
  .btn{display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:8px;font-size:14px;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;border:none;transition:.2s;}
  .btn-primary{background:#1a56db;color:#fff;width:100%;justify-content:center;margin-top:20px;}
  .btn-primary:hover{background:#1e429f;}
  .btn-success{background:#0d9488;color:#fff;width:100%;justify-content:center;margin-top:12px;}
  .alert{padding:12px 16px;border-radius:8px;font-size:13.5px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;}
  .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
  .alert-danger{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
  .section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin:16px 0 8px;}
  .logs-wrap{max-height:280px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:10px;background:#f9fafb;}
  .warn-box{background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:8px;padding:12px 16px;font-size:13px;margin-top:16px;}
</style>
</head>
<body>
<div class="box">
  <div class="box-head">
    <div style="font-size:40px;margin-bottom:8px;">🏛️</div>
    <h1>Barangay Pili System — Installer</h1>
    <p>This will create the database <strong><?= DB_NAME ?></strong> and all required tables.</p>
  </div>
  <div class="box-body">

  <?php if ($step === 'check'): ?>
    <!-- System checks -->
    <div class="section-title">System Requirements</div>
    <?php foreach ($checks as $c): ?>
    <div class="check-row <?= $c['ok']?'ok':'fail' ?>">
      <i class="fas <?= $c['ok']?'fa-check-circle':'fa-times-circle' ?>"></i>
      <?= htmlspecialchars($c['label']) ?>
      <span class="check-val"><?= htmlspecialchars($c['val']) ?></span>
    </div>
    <?php endforeach; ?>

    <div class="section-title" style="margin-top:20px;">Default Credentials After Install</div>
    <div style="background:#f3f4f6;border-radius:8px;padding:14px;font-size:13px;font-family:monospace;">
      <div><strong>Admin:</strong> username = <em>admin</em> &nbsp;|&nbsp; password = <em>password</em></div>
      <div style="margin-top:4px;"><strong>Staff:</strong> username = <em>staff1</em> &nbsp;|&nbsp; password = <em>password</em></div>
    </div>

    <div class="warn-box" style="margin-top:16px;">
      <i class="fas fa-exclamation-triangle"></i>
      <strong>Important:</strong> Make sure XAMPP MySQL is running before clicking Install.
      After installation, <strong>delete this install.php file</strong> for security.
    </div>

    <form method="POST">
      <input type="hidden" name="step" value="install">
      <?php $all_ok = array_reduce($checks, fn($c,$r) => $c && $r['ok'], true); ?>
      <button type="submit" class="btn btn-primary" <?= !$all_ok?'disabled':'' ?>>
        <i class="fas fa-database"></i>
        <?= $all_ok ? 'Install Database Now' : 'Fix Requirements Above First' ?>
      </button>
    </form>

  <?php elseif ($step === 'result'): ?>
    <?php if ($success): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle fa-lg"></i>
      <div>
        <strong>Installation Successful!</strong><br>
        Database <code><?= DB_NAME ?></code> has been created with all tables and seed data.
      </div>
    </div>
    <?php else: ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle fa-lg"></i>
      <div><strong>Some errors occurred.</strong> Check the log below. You may re-run if needed.</div>
    </div>
    <?php endif; ?>

    <div class="section-title">Installation Log</div>
    <div class="logs-wrap">
      <?php foreach ($results as $r): ?>
      <div class="log-row <?= $r['ok']?'ok':'fail' ?>">
        <i class="fas <?= $r['ok']?'fa-check':'fa-times' ?>" style="flex-shrink:0;margin-top:2px;"></i>
        <code><?= htmlspecialchars($r['msg']) ?></code>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($success): ?>
    <a href="index.php" class="btn btn-success">
      <i class="fas fa-sign-in-alt"></i> Go to Login Page
    </a>
    <div class="warn-box">
      <i class="fas fa-shield-alt"></i>
      <strong>Security Notice:</strong> Please delete <code>install.php</code> from your server now that installation is complete.
    </div>
    <?php else: ?>
    <form method="POST">
      <input type="hidden" name="step" value="install">
      <button type="submit" class="btn btn-primary"><i class="fas fa-redo"></i> Retry Installation</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>

  </div>
</div>
</body>
</html>
