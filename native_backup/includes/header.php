<?php
require_once __DIR__ . '/../config.php';
$current = current_user();
$flash = get_flash();
// $page_title injected by caller; $active_nav for sidebar highlight
$page_title = $page_title ?? 'Dashboard';
$active_nav = $active_nav ?? '';
$is_admin = in_array($current['role'], ['admin', 'staff']);
$base = BASE_URL;
$profile_photo = '';
if (!empty($current['id'])) {
  $conn = db_connect();
  if ($is_admin) {
    $stmt = $conn->prepare("SELECT photo FROM users WHERE id=?");
    $stmt->bind_param('i', $current['id']);
  } else {
    $resident_id = (int) ($_SESSION['resident_id'] ?? 0);
    $stmt = $conn->prepare("SELECT photo FROM residents WHERE id=?");
    $stmt->bind_param('i', $resident_id);
  }
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $profile_photo = $row['photo'] ?? '';
  $stmt->close();
  $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($page_title) ?> — <?= APP_NAME ?> System</title>
  <meta name="description" content="Barangay Pili Clearance and Certificate Processing System">
  <link rel="icon" type="image/png" href="<?= $base ?>/assets/images/pili_logo.png">
  <link rel="shortcut icon" href="<?= $base ?>/favicon.ico">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="wrapper">
    <!-- ── Sidebar ─────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <div class="brand-logo" style="background: transparent;">
          <img src="<?= $base ?>/assets/images/pili_logo.png" alt="Pili Logo" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
        </div>
        <div class="brand-text">
          <strong>Brgy. Pili</strong>
          <small>Clearance System</small>
        </div>
      </div>

      <nav class="sidebar-nav">
        <?php if ($is_admin): ?>
          <span class="nav-section-label">Main</span>
          <a href="<?= $base ?>/admin/dashboard.php" class="nav-link <?= $active_nav === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
          </a>
          <span class="nav-section-label">Management</span>
          <a href="<?= $base ?>/admin/requests.php" class="nav-link <?= $active_nav === 'requests' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Requests
          </a>
          <a href="<?= $base ?>/admin/residents.php" class="nav-link <?= $active_nav === 'residents' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Residents
          </a>
          <a href="<?= $base ?>/admin/certificates.php"
            class="nav-link <?= $active_nav === 'certificates' ? 'active' : '' ?>">
            <i class="fas fa-certificate"></i> Certificate Types
          </a>
          <a href="<?= $base ?>/admin/officials.php" class="nav-link <?= $active_nav === 'officials' ? 'active' : '' ?>">
            <i class="fas fa-user-tie"></i> Officials
          </a>
          <?php if ($current['role'] === 'admin'): ?>
            <span class="nav-section-label">Administration</span>
            <a href="<?= $base ?>/admin/users.php" class="nav-link <?= $active_nav === 'users' ? 'active' : '' ?>">
              <i class="fas fa-user-shield"></i> User Management
            </a>
            <a href="<?= $base ?>/admin/archive.php" class="nav-link <?= $active_nav === 'archive' ? 'active' : '' ?>">
              <i class="fas fa-box-archive"></i> Archive
            </a>
            <a href="<?= $base ?>/admin/reports.php" class="nav-link <?= $active_nav === 'reports' ? 'active' : '' ?>">
              <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="<?= $base ?>/admin/activity_logs.php" class="nav-link <?= $active_nav === 'logs' ? 'active' : '' ?>">
              <i class="fas fa-history"></i> Activity Logs
            </a>
          <?php endif; ?>
        <?php else: ?>
          <span class="nav-section-label">My Account</span>
          <a href="<?= $base ?>/resident/request.php" class="nav-link <?= $active_nav === 'request' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> New Request
          </a>
          <a href="<?= $base ?>/resident/my_requests.php"
            class="nav-link <?= $active_nav === 'my_requests' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> My Requests
          </a>
          <a href="<?= $base ?>/resident/profile.php" class="nav-link <?= $active_nav === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> My Profile
          </a>
        <?php endif; ?>

        <span class="nav-section-label">General</span>

        <a href="<?= $base ?>/includes/logout.php" class="nav-link" onclick="return confirm('Log out?')">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </nav>

      <div class="sidebar-footer">
        <a class="user-info" href="<?= $is_admin ? $base . '/admin/profile.php' : $base . '/resident/profile.php' ?>"
          style="text-decoration:none;">
          <div class="user-avatar">
            <?php if ($profile_photo): ?>
              <img src="<?= UPLOAD_URL . htmlspecialchars($profile_photo) ?>" alt="Profile photo">
            <?php else: ?>
              <?= strtoupper(substr($current['name'], 0, 1)) ?>
            <?php endif; ?>
          </div>
          <div>
            <div class="user-name"><?= htmlspecialchars($current['name']) ?></div>
            <div class="user-role"><?= ucfirst($current['role']) ?></div>
          </div>
        </a>
      </div>
    </aside>

    <!-- ── Main ───────────────────────────────────────────── -->
    <div class="main-content">
      <!-- Topbar -->
      <header class="topbar no-print">
        <div class="topbar-left">
          <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;font-size:20px;color:#374151;">
            <i class="fas fa-bars"></i>
          </button>
          <span class="topbar-title"><?= htmlspecialchars($page_title) ?></span>
        </div>
        <div class="topbar-right">
          <a href="<?= $is_admin ? $base . '/admin/profile.php' : $base . '/resident/profile.php' ?>"
            class="topbar-profile-link" title="Profile">
            <span class="topbar-avatar">
              <?php if ($profile_photo): ?>
                <img src="<?= UPLOAD_URL . htmlspecialchars($profile_photo) ?>" alt="Profile photo">
              <?php else: ?>
                <?= strtoupper(substr($current['name'] ?: $current['username'], 0, 1)) ?>
              <?php endif; ?>
            </span>
            <span class="topbar-profile-name"><?= htmlspecialchars($current['name'] ?: $current['username']) ?></span>
          </a>
          <a href="<?= $is_admin ? $base . '/admin/dashboard.php' : $base . '/resident/my_requests.php' ?>"
            class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home"></i>
          </a>
        </div>
      </header>

      <!-- Flash -->
      <?php if ($flash): ?>
        <div id="flash-message" style="margin:16px 24px 0;">
          <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="page-content">