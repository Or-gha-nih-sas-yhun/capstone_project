<?php
require_once __DIR__ . '/../config.php';

// ── Fetch helpers ────────────────────────────────────────
function db_fetch_all(string $sql, string $types = '', ...$params): array {
    $conn = db_connect();
    $stmt = $conn->prepare($sql);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close(); $conn->close();
    return $result;
}

function db_fetch_one(string $sql, string $types = '', ...$params): ?array {
    $conn = db_connect();
    $stmt = $conn->prepare($sql);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close(); $conn->close();
    return $row ?: null;
}

function db_execute(string $sql, string $types, ...$params): bool {
    $conn = db_connect();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $stmt->close(); $conn->close();
    return $ok;
}

function db_insert(string $sql, string $types, ...$params): int {
    $conn = db_connect();
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $id = (int)$conn->insert_id;
    $stmt->close(); $conn->close();
    return $id;
}

// ── Dashboard stats ──────────────────────────────────────
function get_stats(): array {
    $conn = db_connect();
    $stats = [];
    $stats['residents']  = $conn->query("SELECT COUNT(*) c FROM residents WHERE status='active'")->fetch_assoc()['c'];
    $stats['pending']    = $conn->query("SELECT COUNT(*) c FROM requests WHERE status='pending'")->fetch_assoc()['c'];
    $stats['approved']   = $conn->query("SELECT COUNT(*) c FROM requests WHERE status='approved'")->fetch_assoc()['c'];
    $stats['released']   = $conn->query("SELECT COUNT(*) c FROM requests WHERE status='released'")->fetch_assoc()['c'];
    $stats['total_req']  = $conn->query("SELECT COUNT(*) c FROM requests")->fetch_assoc()['c'];
    $stats['revenue']    = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE payment_status='paid'")->fetch_assoc()['s'];
    $stats['pending_residents'] = $conn->query("SELECT COUNT(*) c FROM residents r JOIN users u ON u.resident_id=r.id WHERE r.status='active' AND u.status='inactive'")->fetch_assoc()['c'];
    $conn->close();
    return $stats;
}

// ── Format helpers ───────────────────────────────────────
function format_date(string $date): string {
    return date('F d, Y', strtotime($date));
}
function format_datetime(string $dt): string {
    return date('M d, Y h:i A', strtotime($dt));
}
function status_badge(string $status): string {
    $map = [
        'pending'    => 'warning',
        'processing' => 'info',
        'approved'   => 'success',
        'rejected'   => 'danger',
        'released'   => 'primary',
        'paid'       => 'success',
        'unpaid'     => 'danger',
        'waived'     => 'secondary',
        'active'     => 'success',
        'inactive'   => 'secondary',
        'suspended'  => 'danger',
        'archived'   => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-{$color}\">".ucfirst($status)."</span>";
}

function certificate_template_for(string $certificate_name): string {
    $name = strtolower($certificate_name);
    if (str_contains($name, 'indigency')) return 'certificate_indigency.php';
    if (str_contains($name, 'residency') || str_contains($name, 'resident')) return 'certificate_residency.php';
    if (str_contains($name, 'moral')) return 'certificate_moral.php';
    if (str_contains($name, 'clearance')) return 'certificate_clearance.php';
    return 'certificate.php';
}

function certificate_category_for(string $certificate_name): string {
    $name = strtolower($certificate_name);
    if (str_contains($name, 'business')) return 'Business';
    if (str_contains($name, 'jobseeker')) return 'Employment';
    if (str_contains($name, 'indigency')) return 'Social Services';
    if (str_contains($name, 'moral') || str_contains($name, 'residency')) return 'Certification';
    return 'Clearance';
}

// ── Pagination ───────────────────────────────────────────
function paginate(int $total, int $per_page, int $current_page): array {
    $total_pages = max(1, ceil($total / $per_page));
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    return ['total' => $total, 'total_pages' => $total_pages,
            'current' => $current_page, 'per_page' => $per_page, 'offset' => $offset];
}

// ── Upload helper ────────────────────────────────────────
function upload_file(array $file, string $subfolder = ''): ?string {
    $allowed = ['jpg','jpeg','png','gif','pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    $dir = UPLOAD_PATH . ltrim($subfolder, '/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = $dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return ltrim($subfolder, '/') . '/' . $filename;
    }
    return null;
}

// ── Age calculator ───────────────────────────────────────
function calculate_age(string $birthdate): int {
    return (int)(new DateTime($birthdate))->diff(new DateTime())->y;
}
