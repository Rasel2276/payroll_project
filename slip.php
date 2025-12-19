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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Login Slip - <?php echo htmlspecialchars($name); ?></title>
    <style>
        :root {
            --primary-color: #4BB543;
            --dark-bg: #191c24;
            --card-bg: #ffffff;
            --text-main: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .slip-card {
            width: 100%;
            max-width: 500px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .header {
            background: var(--dark-bg);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header p {
            margin: 10px 0 0;
            opacity: 0.8;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #777;
            font-weight: 600;
            font-size: 14px;
        }

        .value {
            color: var(--text-main);
            font-weight: 700;
            font-size: 15px;
            text-align: right;
        }

        .password-box {
            background: #f9f9f9;
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }

        .password-box .pwd {
            display: block;
            font-size: 22px;
            color: var(--primary-color);
            letter-spacing: 2px;
            font-family: 'Courier New', Courier, monospace;
        }

        .footer-note {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 10px;
            line-height: 1.4;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        button, .back-btn {
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            border: none;
        }

        .print-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .print-btn:hover {
            background-color: #3a9a35;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        /* প্রিন্ট করার সময় যা যা হবে */
        @media print {
            body { background: white; padding: 0; }
            .actions { display: none; }
            .slip-card { box-shadow: none; border: 1px solid #333; margin: 0 auto; }
            .header { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="slip-card">
    <div class="header">
        <h2>Your Company Name</h2>
        <p>Official Employee Access Slip</p>
    </div>

    <div class="content">
        <div class="info-row">
            <span class="label">Name:</span>
            <span class="value"><?php echo htmlspecialchars($name); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Email Address:</span>
            <span class="value"><?php echo htmlspecialchars($email); ?></span>
        </div>
        <div class="info-row">
            <span class="label">Access Level:</span>
            <span class="value">Employee Dashboard</span>
        </div>

        <div class="password-box">
            <span class="label" style="display:block; margin-bottom:5px;">One-Time Password</span>
            <strong class="pwd"><?php echo htmlspecialchars($password); ?></strong>
        </div>

        <div class="info-row">
            <span class="label">Login Portal:</span>
            <span class="value" style="font-size: 12px;">
                <?php echo (isset($_SERVER['HTTP_HOST']) ? 'http://'.$_SERVER['HTTP_HOST'] : '') . dirname($_SERVER['REQUEST_URI']) . '/index.php'; ?>
            </span>
        </div>

        <p class="footer-note">
            Please keep this information confidential. You will be required to change your password after your first successful login.
        </p>
    </div>
</div>

<div class="actions">
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
    <a href="/payroll/admindashboard/index.php" class="back-btn">Add Another</a>
</div>

</body>
</html>