<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header("Location: index.php"); 
    exit; 
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// DB Connection
$host = 'localhost';
$db = 'payroll';
$db_user = 'root';
$db_pass = '';
$conn = new mysqli($host, $db_user, $db_pass, $db);

if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Invalid login
if (!$userData || $userData['password'] !== $password) {
    header("Location: index.php?error=" . urlencode('Invalid email or password'));
    exit;
}

// ✅ Set session using auth_user array
$_SESSION['auth_user'] = [
    'id' => $userData['id'],
    'name' => $userData['name'],
    'email' => $userData['email'],
    'role' => $userData['role'],
    'must_change_password' => $userData['must_change_password']
];

// First-time login → force change password
if ($userData['must_change_password'] == 1 && $userData['role'] == 'employee') {
    header("Location: change_password.php");
    exit;
}

// Normal redirect
if ($userData['role'] === 'admin') {
    header("Location: admindashboard/index.php");
} else {
    header("Location: employeedashboard/index.php");
}
exit;
?>
