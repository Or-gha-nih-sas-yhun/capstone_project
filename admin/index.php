<?php
/**
 * Forward Hostinger subdomain requests to Laravel's public/index.php
 */

if (file_exists(__DIR__ . '/../public/index.php')) {
    require __DIR__ . '/../public/index.php';
} elseif (file_exists(__DIR__ . '/public/index.php')) {
    require __DIR__ . '/public/index.php';
} else {
    // Graceful fallback: Redirect to main domain admin login
    header('Location: https://brgypilieclearance.com/admin/login');
    exit;
}
