<?php
/**
 * Forward Hostinger subdomain requests to Laravel's public/index.php
 */

if (file_exists(__DIR__ . '/../public/index.php')) {
    require __DIR__ . '/../public/index.php';
} elseif (file_exists(__DIR__ . '/public/index.php')) {
    require __DIR__ . '/public/index.php';
} else {
<<<<<<< HEAD
    // Graceful fallback: Redirect to main domain admin login
    header('Location: https://brgypilieclearance.com/admin/login');
=======
    // Graceful fallback: redirect to the main domain's admin login. The
    // parent domain is derived from the current host by dropping the "admin."
    // prefix, so this works for any barangay's domain. Laravel's config is
    // not available here -- that is the case this branch handles.
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $parent = preg_replace('/^admin\./i', '', $host);
    $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';

    header('Location: ' . $scheme . '://' . $parent . '/admin/login');
>>>>>>> origin/master
    exit;
}
