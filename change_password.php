<?php
session_start();

if (!isset($_SESSION['auth_user']) || $_SESSION['auth_user']['role'] !== 'employee') {
    header("Location: ../index.php");
    exit;
}

$host = 'localhost';
$db = 'payroll';
$db_user = 'root';
$db_pass = '';
$conn = new mysqli($host,$db_user,$db_pass,$db);
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpass = trim($_POST['newpass'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($newpass === '' || $confirm === '') {
        $error = "Both fields required!";
    } elseif ($newpass !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        $uid = $_SESSION['auth_user']['id'];
        $stmt = $conn->prepare("UPDATE users SET password=?, must_change_password=0 WHERE id=?");
        $stmt->bind_param("si",$newpass,$uid);
        $stmt->execute();

        $_SESSION['auth_user']['must_change_password'] = 0;

        header("Location: employeedashboard/index.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8">
<title>Change Password</title>
<style>
body{background:#000; color:#fff; font-family: Arial, sans-serif;}
.container{background:#191C24; width:400px; margin:100px auto; padding:30px; border-radius:8px;}
input[type=password]{width:100%; padding:8px; margin:8px 0; border-radius:4px; border:none;}
button{padding:10px 20px; border:none; border-radius:4px; background:#00b894; color:#fff; cursor:pointer;}
p.error{color:red;}
</style>
</head>
<body>
<div class="container">
<h2>Change Your Password (First Login)</h2>

<?php if(isset($error)) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>

<form method="POST" action="">
    <label>New Password: <input type="password" name="newpass" required></label><br>
    <label>Confirm Password: <input type="password" name="confirm" required></label><br>
    <button type="submit">Update Password</button>
</form>
</div>
</body>
</html>
