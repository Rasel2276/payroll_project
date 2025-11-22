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
body {
    background: url('includes/login_background_image.png') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
    color:#fff;
}

.login-box {
    background: rgba(25, 28, 36, 0.2); /* transparent with 20% opacity */
    backdrop-filter: blur(10px);       /* blur effect */
    -webkit-backdrop-filter: blur(10px);
    width:400px;
    padding:35px 40px;
    border-radius:10px;
    box-shadow:0 0 20px rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.1);
}


        h2 {
            text-align:center;
            margin-bottom:25px;
            font-weight:600;
            font-size:22px;
        }

        label {
            font-size:14px;
            display:block;
            margin-bottom:5px;
        }

        input[type=email],
        input[type=password] {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border-radius:6px;
            border:1px solid #2d323e;
            background:#0f1117;
            color:#fff;
            font-size:14px;
            box-sizing:border-box;
        }

        input:focus {
            outline:none;
            border-color:#00b894;
        }

        button {
            width:100%;
            padding:12px;
            background:#00b894;
            color:#fff;
            border:none;
            border-radius:6px;
            font-size:15px;
            cursor:pointer;
            font-weight:bold;
            transition:0.3s;
        }

        button:hover {
            background:#019d7e;
        }

        p.error {
            color:#ff4d4d;
            text-align:center;
            margin-bottom:15px;
            font-size:14px;
        }

        p.default {
            text-align:center;
            margin-top:15px;
            font-size:13px;
            color:#bbb;
        }
    </style>

</head>
<body>

<div class="login-box">

<h2>Payroll System</h2>

<?php if(isset($_GET['error'])): ?>
<p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<form method="POST" action="login.php">
    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<p class="default">Default admin: <br> admin@company.com / admin123</p>

</div>
</body>
</html>
