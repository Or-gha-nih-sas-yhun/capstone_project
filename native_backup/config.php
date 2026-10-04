<?php
// ============================================================
// Brgy. Pili Clearance System — Configuration
// ============================================================

// -------------------------------------------------------
// Environment-Aware Configuration
// -------------------------------------------------------
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Local XAMPP settings
    define('DB_HOST',     'localhost');
    define('DB_USER',     'root');
    define('DB_PASS',     '');
    define('DB_NAME',     'brgy_pili_clearance');
    define('BASE_URL',    'http://localhost/Brgy.pili_clearance');
} else {
    // InfinityFree Production settings
    define('DB_HOST',     'sql310.infinityfree.com');
    define('DB_USER',     'if0_42086556');
    define('DB_PASS',     'your_database_password_here');
    define('DB_NAME',     'if0_42086556_pili');
    define('BASE_URL',    'https://brgypili.infinityfree.me');
}

define('DB_CHARSET',  'utf8mb4');

define('APP_NAME',    'Barangay Pili');
define('APP_TAGLINE', 'Clearance & Certificate Processing System');
define('APP_VERSION', '1.0.0');

define('UPLOAD_PATH', __DIR__ . '/assets/uploads/');
define('UPLOAD_URL',  BASE_URL . '/assets/uploads/');

define('SESSION_TIMEOUT', 3600); // 1 hour

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------
// Database Connection (MySQLi)
// -------------------------------------------------------
function db_connect(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log('DB Connection failed: ' . $conn->connect_error);
        die(json_encode(['error' => 'Database connection failed.']));
    }
    $conn->set_charset(DB_CHARSET);

    // Auto-migrate small schema additions used by newer screens.
    $res = $conn->query("SHOW COLUMNS FROM payments LIKE 'proof_of_payment'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE payments ADD COLUMN proof_of_payment VARCHAR(255) NULL");
    }

    $res = $conn->query("SHOW COLUMNS FROM certificates LIKE 'category'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE certificates ADD COLUMN category VARCHAR(80) NOT NULL DEFAULT 'General' AFTER description");
    }

    $res = $conn->query("SHOW COLUMNS FROM users LIKE 'photo'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL AFTER resident_id");
    }

    $res = $conn->query("SHOW COLUMNS FROM officials LIKE 'photo'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE officials ADD COLUMN photo VARCHAR(255) NULL AFTER position");
    }

    foreach (['residents', 'users', 'certificates', 'requests'] as $table) {
        $res = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'archived_at'");
        if ($res && $res->num_rows === 0) {
            $conn->query("ALTER TABLE {$table} ADD COLUMN archived_at DATETIME NULL");
        }
        $res = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'archived_by'");
        if ($res && $res->num_rows === 0) {
            $conn->query("ALTER TABLE {$table} ADD COLUMN archived_by INT NULL");
        }
    }

    $conn->query("UPDATE certificates SET category = CASE
        WHEN LOWER(name) LIKE '%business%' THEN 'Business'
        WHEN LOWER(name) LIKE '%jobseeker%' THEN 'Employment'
        WHEN LOWER(name) LIKE '%indigency%' THEN 'Social Services'
        WHEN LOWER(name) LIKE '%moral%' OR LOWER(name) LIKE '%residency%' THEN 'Certification'
        ELSE 'Clearance' END
        WHERE category IS NULL OR category='' OR category='General'");
    $conn->query("UPDATE certificates SET template_file = CASE
        WHEN LOWER(name) LIKE '%indigency%' THEN 'certificate_indigency.php'
        WHEN LOWER(name) LIKE '%residency%' OR LOWER(name) LIKE '%resident%' THEN 'certificate_residency.php'
        WHEN LOWER(name) LIKE '%moral%' THEN 'certificate_moral.php'
        WHEN LOWER(name) LIKE '%clearance%' THEN 'certificate_clearance.php'
        ELSE 'certificate.php' END
        WHERE template_file IS NULL OR template_file=''");

    return $conn;
}

// -------------------------------------------------------
// Session Helpers
// -------------------------------------------------------
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login(string $redirect = BASE_URL . '/index.php'): void {
    if (!is_logged_in()) {
        header('Location: ' . $redirect);
        exit;
    }
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: ' . $redirect . '?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function require_role(string $role, string $redirect = BASE_URL . '/index.php'): void {
    require_login($redirect);
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . $redirect . '?unauthorized=1');
        exit;
    }
}

function current_user(): array {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'username' => $_SESSION['username']  ?? '',
        'role'     => $_SESSION['user_role'] ?? '',
        'name'     => $_SESSION['full_name'] ?? '',
    ];
}

// -------------------------------------------------------
// Security Helpers
// -------------------------------------------------------
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function is_letters_only(string $input): bool {
    // Allows letters, spaces, hyphens, periods, and commas.
    return empty($input) || preg_match('/^[a-zA-Z\s\-\.,]+$/', $input) === 1;
}

function generate_tracking_number(): string {
    return 'PILI-' . strtoupper(date('Ymd')) . '-' . strtoupper(substr(uniqid(), -6));
}

// -------------------------------------------------------
// Activity Logger
// -------------------------------------------------------
function log_activity(string $action, string $module = '', string $description = ''): void {
    if (!is_logged_in()) return;
    $conn = db_connect();
    $user_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $user_id, $action, $module, $description, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// -------------------------------------------------------
// Flash Messages
// -------------------------------------------------------
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
