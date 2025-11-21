<?php
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';
$password = $_GET['password'] ?? '';
if (!$name || !$email || !$password) {
    echo "Missing parameters.";
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login Slip</title>
<style>
body{font-family: Arial, sans-serif; padding:20px;}
.container{width:600px; margin:0 auto; border:1px solid #333; padding:20px;}
.header{text-align:center;}
.details{margin-top:20px;}
.details p{line-height:1.6;}
.print-btn{margin-top:20px;}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Company Name</h2>
    <p><strong>Employee Login Access</strong></p>
  </div>
  <div class="details">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>One-Time Password:</strong> <?php echo htmlspecialchars($password); ?></p>
    <p><strong>Login URL:</strong> <?php echo (isset($_SERVER['HTTP_HOST']) ? 'http://'.$_SERVER['HTTP_HOST'] : '') . dirname($_SERVER['REQUEST_URI']) . '/index.php'; ?></p>
  </div>
  <div class="print-btn">
    <button onclick="window.print()">Print / Save as PDF</button>
    <a href="/payroll/admindashboard/pages/add_employee.php">Back to Add Employee</a>
  </div>
</div>
</body>
</html>
