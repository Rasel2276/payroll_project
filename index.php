<?php
session_start();

if (isset($_SESSION['auth_user'])) {
    if ($_SESSION['auth_user']['role'] === 'admin') {
        header("Location: admindashboard/index.php");
    } else {
        header("Location: employeedashboard/index.php");
    }
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Login</title>
    <style>
        body { background:#000; font-family: Arial, sans-serif; color:#fff; }
        .login-box { background:#191C24; width:400px; margin:100px auto; padding:30px; border-radius:8px; }
        input[type=email], input[type=password] { width:100%; padding:8px; margin:8px 0; border-radius:4px; border:none; }
        button { padding:10px 20px; border:none; border-radius:4px; background:#00b894; color:#fff; cursor:pointer; }
        p.error { color:red; }
    </style>
</head>
<body>
<div class="login-box">
<h2>Payroll System — Login</h2>

<?php if(isset($_GET['error'])): ?>
<p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<form method="POST" action="login.php">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
</form>

<p>Default admin: admin@company.com / admin123</p>
</div>
</body>
</html>


