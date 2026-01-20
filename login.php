<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header("Location: index.php"); 
    exit; 
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';


$host = 'localhost';
$db = 'payroll';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();


if (!$userData || $userData['password'] !== $password) {
    header("Location: index.php?error=" . urlencode('Invalid email or password'));
    exit;
}


$_SESSION['auth_user'] = [
    'id'            => $userData['id'],
    'name'          => $userData['name'],
    'email'         => $userData['email'],
    'role'          => $userData['role'],
    'profile_image' => $userData['profile_image'],
    'must_change_password' => $userData['must_change_password']
];


if ($userData['must_change_password'] == 1 && $userData['role'] == 'employee') {
    header("Location: change_password.php");
    exit;
}


if ($userData['role'] === 'admin') {
    header("Location: admindashboard/index.php");
} else {
    header("Location: employeedashboard/index.php");
}

exit;
?>