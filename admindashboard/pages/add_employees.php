<?php include '../includes/header.php'; ?>

<div class="main-panel">
<div class="content-wrapper">
  <?php
require_once __DIR__ . '/../../includes/admin_auth.php';

// Database connection
$host = 'localhost';
$db = 'payroll';
$user = 'root';   // change if needed
$pass = '';       // change if needed
$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (!$name || !$email) {
        $error = "Provide name and email.";
    } else {
        // Generate random 8-character password
        $password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);
        
        // Insert employee into DB
        $stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
        $role = 'employee';
        $stmt->bind_param("ssss",$name,$email,$password,$role);
        if ($stmt->execute()) {
            $success = true;
            $slipUrl = dirname($_SERVER['REQUEST_URI']) . '/../../slip.php?name='.urlencode($name).'&email='.urlencode($email).'&password='.urlencode($password);
        } else {
            $error = "Error adding employee: ".$stmt->error;
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Employee</title>
</head>
<body>
<h2>Add Employee (Admin)</h2>

<?php if(isset($error)) echo "<p style='color:red;'>".htmlspecialchars($error)."</p>"; ?>
<?php if(isset($success) && $success): ?>
    <p>Employee added. 
    <a href="<?php echo htmlspecialchars($slipUrl); ?>" target="_blank">Open Login Slip (Print → Save as PDF)</a></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Name: <input type="text" name="name" required></label><br><br>
    <label>Email: <input type="email" name="email" required></label><br><br>
    <button type="submit">Create Employee</button>
</form>

<p><a href="/payroll/admindashboard/index.php">Back to Admin Dashboard</a></p>
</body>
</html>
</div>
<?php include '../includes/footer.php'; ?>