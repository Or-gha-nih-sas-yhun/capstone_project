<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
  $role = $_SESSION['user_role'];
  header('Location: ' . BASE_URL . ($role === 'resident' ? '/resident/dashboard.php' : '/admin/dashboard.php'));
  exit;
}

$error = '';
$success = '';
$timeout = isset($_GET['timeout']);

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $username = sanitize($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!$username || !$password) {
    $error = 'Please enter your username and password.';
  } else {
    $login_result = attempt_login($username, $password);
    if ($login_result === 'success') {
      $role = $_SESSION['user_role'];
      header('Location: ' . BASE_URL . ($role === 'resident' ? '/resident/dashboard.php' : '/admin/dashboard.php'));
      exit;
    } elseif ($login_result === 'pending') {
      $error = 'Your account is pending approval by the administrator.';
    } elseif ($login_result === 'suspended') {
      $error = 'Your account has been suspended.';
    } else {
      $error = 'Invalid username or password. Please try again.';
    }
  }
}

// Handle registration POST
$reg_errors = [];
$reg_tab_active = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  $reg_tab_active = true;
  $result = register_resident($_POST);
  if ($result['success']) {
    $success = $result['msg'];
    $reg_tab_active = false;
  } else {
    $error = $result['msg'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Barangay Pili — Clearance & Certificate System</title>
  <meta name="description" content="Official online portal for Barangay Pili clearance and certificate requests.">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/pili_logo.png">
  <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left panel -->
      <div class="auth-left">
        <div class="brgy-seal" style="text-align: left; margin-bottom: 24px;">
          <img src="<?= BASE_URL ?>/assets/images/pili_logo.png" alt="Barangay Logo"
            style="width: 120px; height: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
        </div>

        <h1>Barangay Pili<br>Streamlined Clearance and Certificate </h1>
        <p>Your one-stop portal for barangay clearances and certificates.</p>
        <div class="feature-list">
          <div class="feature-item"><i class="fas fa-file-shield"></i> Barangay Clearance</div>
          <div class="feature-item"><i class="fas fa-certificate"></i> Various Certificates</div>
          <div class="feature-item"><i class="fas fa-clock"></i> Real-Time Status Tracking</div>
          <div class="feature-item"><i class="fas fa-print"></i> Print Documents</div>
          <div class="feature-item"><i class="fas fa-lock"></i> Secure &amp; Private</div>
        </div>
        <div
          style="margin-top:32px;padding-top:20px;border-top:1px solid rgba(255,255,255,.2);font-size:12px;opacity:.6;">
          Barangay Pili, Madridejos ,Cebu &bull; v1.0.0
        </div>
      </div>

      <!-- Right panel -->
      <div class="auth-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your account or register as a new resident.</p>

        <!-- Tabs -->
        <div class="auth-tabs">
          <button class="auth-tab <?= !$reg_tab_active ? 'active' : '' ?>" onclick="switchTab('tab-login',this)"
            id="btn-login">
            <i class="fas fa-sign-in-alt"></i> Sign In
          </button>
          <button class="auth-tab <?= $reg_tab_active ? 'active' : '' ?>" onclick="switchTab('tab-register',this)"
            id="btn-register">
            <i class="fas fa-user-plus"></i> Register
          </button>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($timeout): ?>
          <div class="alert alert-warning"><i class="fas fa-clock"></i> Session expired. Please log in again.</div>
        <?php endif; ?>

        <!-- Login Tab -->
        <div id="tab-login" class="tab-panel" style="display:<?= $reg_tab_active ? 'none' : 'block' ?>;">
          <form method="POST">
            <div class="form-group">
              <label class="form-label" for="username">Username or Email</label>
              <input type="text" id="username" name="username" class="form-control"
                placeholder="Enter your username or email" required autocomplete="username">
            </div>
            <div class="form-group">
              <label class="form-label" for="password">Password</label>
              <div style="position:relative;">
                <input type="password" id="password" name="password" class="form-control"
                  placeholder="Enter your password" required autocomplete="current-password">
                <button type="button" onclick="togglePw('password')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#6b7280;">
                  <i class="fas fa-eye" id="pw-icon"></i>
                </button>
              </div>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100" style="margin-top:8px;">
              <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
          </form>
        </div>

        <!-- Register Tab -->
        <div id="tab-register" class="tab-panel" style="display:<?= $reg_tab_active ? 'block' : 'none' ?>;">
          <form method="POST">
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" required placeholder="Juan"
                  pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed"
                  oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
              </div>
              <div class="form-group">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" required placeholder="Dela Cruz"
                  pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed"
                  oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Middle Name</label>
              <input type="text" name="middle_name" class="form-control" placeholder="Optional"
                pattern="[A-Za-z\s\-\.,]+" title="Only letters, spaces, hyphens, and periods are allowed"
                oninput="this.value = this.value.replace(/[^A-Za-z\s\-\.,]/g, '')">
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select" required>
                  <option value="">Select</option>
                  <option>Male</option>
                  <option>Female</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Birthdate *</label>
                <input type="date" name="birthdate" class="form-control" min="1900-01-01" max="<?= date('Y-m-d') ?>"
                  required>
              </div>

              <div class="form-group">
                <label class="form-label">Civil Status *</label>
                <select name="civil_status" class="form-select" required>
                  <option value="">Select</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Widowed</option>
                  <option>Separated</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" placeholder="09XXXXXXXXX">
              </div>



              <div class="form-group">
                <label class="form-label">Purok</label>
                <input type="text" name="purok" class="form-control" placeholder="e.g. Purok 1">
              </div>
              <div class="form-group">
                <label class="form-label">Years of Residency</label>
                <input type="number" name="years_of_residency" class="form-control" min="0" value="0">
              </div>


              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="you@email.com">
              </div>

              <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" required placeholder="username">
              </div>
              <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required placeholder="Min 6 characters"
                  minlength="6">
              </div>

              <button type="submit" name="register" class="btn btn-primary w-50">
                <i class="fas fa-user-plus"></i> Create Account
              </button>
          </form>
        </div>

      </div><!-- /.auth-right -->
    </div><!-- /.auth-container -->
  </div><!-- /.auth-page -->

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script>
    function togglePw(id) {
      const el = document.getElementById(id);
      const icon = document.getElementById('pw-icon');
      if (el.type === 'password') { el.type = 'text'; icon.className = 'fas fa-eye-slash'; }
      else { el.type = 'password'; icon.className = 'fas fa-eye'; }
    }
  </script>
</body>

</html>