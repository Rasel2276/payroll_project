<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) { die("Error: Payslip ID missing."); }
$id = intval($_GET['id']);


$sql = "SELECT p.*, u.name, u.designation, u.bank_name, u.bank_account, u.present_address 
        FROM payslips p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) { die("Error: Payslip not found."); }
$ps = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip_<?php echo htmlspecialchars($ps['name']) . "_" . $ps['month']; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            width: 850px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

     
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #444;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-info h2 {
            margin: 0;
            font-size: 26px;
            color: #1a73e8;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 5px 0;
            font-size: 13px;
            color: #666;
        }

        .payslip-title {
            text-align: right;
        }

        .payslip-title h3 {
            margin: 0;
            font-size: 20px;
            color: #444;
        }

     
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .info-table {
            width: 48%;
            font-size: 14px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 0;
        }

        .info-table td:first-child {
            font-weight: bold;
            color: #555;
            width: 140px;
        }

     
        .salary-container {
            display: flex;
            justify-content: space-between;
            border: 1px solid #ddd;
        }

        .salary-column {
            width: 50%;
        }

        .salary-column:first-child {
            border-right: 1px solid #ddd;
        }

        .column-header {
            background: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #ddd;
            text-transform: uppercase;
            font-size: 13px;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .salary-table td {
            padding: 10px;
            border-bottom: 1px dotted #eee;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .total-row {
            background: #fcfcfc;
            font-weight: bold;
        }

       
        .net-salary-box {
            margin-top: 20px;
            background: #1a73e8;
            color: #fff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 4px;
        }

        .net-salary-box h2 {
            margin: 0;
            font-size: 18px;
        }

        .net-salary-box span {
            font-size: 22px;
            font-weight: bold;
        }

   
        .bank-info {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
            font-size: 13px;
        }


        .signature-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .no-print-btn {
            background: #4BB543;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .container { box-shadow: none; border: none; width: 100%; }
            .no-print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div style="text-align: center;" class="no-print">
        <button onclick="window.print()" class="no-print-btn">Click to Print Payslip</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-info">
                <h2>Digital Payroll Ltd.</h2>
                <p>House 12, Road 5, Dhanmondi, Dhaka</p>
                <p>Email: support@digitalpayroll.com | Web: www.payroll.com</p>
            </div>
            <div class="payslip-title">
                <h3>PAYSLIP</h3>
                <p style="margin: 5px 0; font-weight: bold; color: #1a73e8;">
                    <?php echo strtoupper($ps['month']) . " " . $ps['year']; ?>
                </p>
            </div>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr><td>Employee Name</td><td>: <?php echo htmlspecialchars($ps['name']); ?></td></tr>
                <tr><td>Designation</td><td>: <?php echo htmlspecialchars($ps['designation']); ?></td></tr>
                <tr><td>Employee ID</td><td>: EMP-<?php echo str_pad($ps['user_id'], 4, '0', STR_PAD_LEFT); ?></td></tr>
            </table>
            <table class="info-table">
                <tr><td>Salary Date</td><td>: <?php echo date('d M, Y', strtotime($ps['created_at'])); ?></td></tr>
                <tr><td>Payment Mode</td><td>: Bank Transfer</td></tr>
                <tr><td>Location</td><td>: Dhaka, Bangladesh</td></tr>
            </table>
        </div>

        <div class="salary-container">
            <div class="salary-column">
                <div class="column-header">Earnings</div>
                <table class="salary-table">
                    <tr><td>Basic Salary</td><td class="amount"><?php echo number_format($ps['basic_salary'], 2); ?></td></tr>
                    <tr><td>Medical Allowance</td><td class="amount"><?php echo number_format($ps['medical_allowance'], 2); ?></td></tr>
                    <tr><td>House Rent</td><td class="amount"><?php echo number_format($ps['house_rent'], 2); ?></td></tr>
                    <tr><td>Overtime Pay</td><td class="amount"><?php echo number_format($ps['overtime_amount'], 2); ?></td></tr>
                    <tr class="total-row"><td>Gross Earnings</td><td class="amount"><?php echo number_format($ps['gross_salary'], 2); ?></td></tr>
                </table>
            </div>
            <div class="salary-column">
                <div class="column-header">Deductions</div>
                <table class="salary-table">
                    <tr><td>Absent Deduction</td><td class="amount"><?php echo number_format($ps['absent_deduction'], 2); ?></td></tr>
                    <tr><td>Loan Repayment</td><td class="amount"><?php echo number_format($ps['home_loan_deduction'], 2); ?></td></tr>
                    <tr><td>Taxes / Other</td><td class="amount">0.00</td></tr>
                    <tr style="height: 41px;"><td></td><td></td></tr> <tr class="total-row"><td>Total Deductions</td><td class="amount"><?php echo number_format($ps['total_deduction'], 2); ?></td></tr>
                </table>
            </div>
        </div>

        <div class="net-salary-box">
            <h2>NET SALARY PAYABLE</h2>
            <span>BDT <?php echo number_format($ps['net_salary'], 2); ?></span>
        </div>

        <div class="bank-info">
            <strong>Payment Method:</strong> Disbursed via Bank Transfer to 
            <strong><?php echo $ps['bank_name']; ?></strong> | 
            Account No: <strong><?php echo $ps['bank_account']; ?></strong>
        </div>

        <div class="signature-section">
            <div class="sig-box">Employee Signature</div>
            <div class="sig-box">Authorized Signatory</div>
        </div>

        <div style="margin-top: 50px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 10px;">
            This is a computer-generated payslip and does not require a physical seal.
        </div>
    </div>

</body>
</html>