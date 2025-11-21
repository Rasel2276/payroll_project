<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// auth_user session চেক করো
if (!isset($_SESSION['auth_user']) || $_SESSION['auth_user']['role'] !== 'employee') {
    header("Location: ../index.php?error=" . urlencode('Please login as employee'));
    exit;
}
?>
