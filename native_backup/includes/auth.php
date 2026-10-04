<?php
require_once __DIR__ . '/../config.php';

// ── Login ─────────────────────────────────────────────────
function attempt_login(string $username, string $password): string {
    $conn = db_connect();
    $stmt = $conn->prepare(
        "SELECT u.*, CONCAT(r.first_name,' ',r.last_name) AS full_name
         FROM users u
         LEFT JOIN residents r ON u.resident_id = r.id
         WHERE u.username = ? OR u.email = ?
         LIMIT 1"
    );
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'inactive') {
            return 'pending';
        }
        if ($user['status'] === 'suspended') {
            return 'suspended';
        }
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['full_name']  = $user['full_name'] ?: $user['username'];
        $_SESSION['resident_id']= $user['resident_id'];
        $_SESSION['last_activity'] = time();
        log_activity('LOGIN', 'Auth', 'User logged in');
        return 'success';
    }
    return 'failed';
}

// ── Logout ────────────────────────────────────────────────
function logout(): void {
    log_activity('LOGOUT', 'Auth', 'User logged out');
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// ── Register resident account ─────────────────────────────
function register_resident(array $data): array {
    // Validate required
    $required = ['first_name','last_name','email','username','password','birthdate','gender','civil_status','address'];
    foreach ($required as $f) {
        if (empty($data[$f])) return ['success' => false, 'msg' => "Field '$f' is required."];
    }

    // Check duplicate
    $exist = db_fetch_one("SELECT id FROM users WHERE username=? OR email=?", 'ss', $data['username'], $data['email']);
    if ($exist) return ['success' => false, 'msg' => 'Username or email already exists.'];

    if (!is_letters_only($data['first_name']) || !is_letters_only($data['last_name']) || !is_letters_only($data['middle_name'] ?? '')) {
        return ['success' => false, 'msg' => 'First, middle, and last names must contain only letters.'];
    }

    $conn  = db_connect();
    $conn->begin_transaction();
    try {
        // Insert resident
        $stmt = $conn->prepare(
            "INSERT INTO residents (first_name,middle_name,last_name,gender,birthdate,civil_status,
             contact_number,email,address,purok,voter_status,years_of_residency)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $middle_name = $data['middle_name'] ?? '';
        $contact_number = $data['contact_number'] ?? '';
        $purok = $data['purok'] ?? '';
        $voter_status = $data['voter_status'] ?? 'Not Registered';
        $years_of_residency = (int)($data['years_of_residency'] ?? 0);

        $stmt->bind_param('sssssssssssi',
            $data['first_name'], $middle_name,
            $data['last_name'], $data['gender'], $data['birthdate'],
            $data['civil_status'], $contact_number,
            $data['email'], $data['address'], $purok,
            $voter_status,
            $years_of_residency
        );
        $stmt->execute();
        $resident_id = $conn->insert_id;
        $stmt->close();

        // Insert user
        $hash  = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt2 = $conn->prepare(
            "INSERT INTO users (username,email,password,role,resident_id,status) VALUES (?,?,?,'resident',?,'inactive')"
        );
        $stmt2->bind_param('sssi', $data['username'], $data['email'], $hash, $resident_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->commit();
        $conn->close();
        return ['success' => true, 'msg' => 'Registration successful! Your account is pending approval by the administrator.'];
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'msg' => 'Registration failed. Please try again.'];
    }
}
