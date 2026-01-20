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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Login | Premium Access</title>

    <style>
        :root {
            --bg-dark: #0f1015;
            --card-dark: #191c24;
            --accent-green: #00b894;
            --accent-hover: #009475;
            --text-gray: #6c7293;
            --border-color: #2c2e33;
        }

        body {
            background: radial-gradient(circle at center, #1e2129 0%, #0f1015 100%);
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: #ffffff;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* অ্যানিমেটেড বর্ডার কন্টেইনার */
        .login-box {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: var(--card-dark);
            border-radius: 15px;
            padding: 40px;
            box-sizing: border-box;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            overflow: hidden; /* অ্যানিমেশন মাস্ক করার জন্য */
            border: 1px solid var(--border-color);
        }

        /* বর্ডার অ্যানিমেশনের লজিক */
        .login-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                transparent, 
                transparent, 
                transparent, 
                var(--accent-green)
            );
            animation: rotateBorder 4s linear infinite;
            z-index: 0;
        }

        /* ভেতরের বডিকে উপরে রাখার জন্য */
        .login-box::after {
            content: '';
            position: absolute;
            inset: 2px; /* বর্ডারের পুরুত্ব এখান থেকে নিয়ন্ত্রণ করবেন (২ পিক্সেল চিকন বর্ডার) */
            background: var(--card-dark);
            border-radius: 13px;
            z-index: 0;
        }

        /* কন্টেন্টগুলোকে অ্যানিমেশনের উপরে রাখার জন্য */
        .login-header, .error-msg, form, .default-info {
            position: relative;
            z-index: 1;
        }

        @keyframes rotateBorder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 1px;
            color: #ffffff;
            text-transform: uppercase;
        }

        .login-header p {
            color: var(--text-gray);
            font-size: 14px;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 13px;
            display: block;
            margin-bottom: 8px;
            color: #adb5bd;
            font-weight: 500;
        }

        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: #2a3038;
            color: #ffffff;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-green);
            background: #313741;
            box-shadow: 0 0 8px rgba(0, 184, 148, 0.2);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--accent-green);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-transform: uppercase;
        }

        button:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 184, 148, 0.3);
        }

        .error-msg {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #ff4d4d;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .default-info {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 13px;
            color: var(--text-gray);
        }

        .default-info strong {
            color: var(--accent-green);
        }

        @media (max-width: 480px) {
            body { padding: 15px; }
            .login-box { padding: 30px 20px; }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-header">
        <h2>PAYROLL</h2>
        <p>Sign in to manage your workspace</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
    <div class="error-msg">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit">Log In</button>
    </form>

    <div class="default-info">
        Default admin access: <br>
        <strong>admin@company.com</strong> / <strong>admin123</strong>
    </div>
</div>

</body>
</html>