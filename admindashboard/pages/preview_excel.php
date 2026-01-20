<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; 
$db   = 'payroll'; 
$user = 'root'; 
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

$type   = $_GET['report_type'] ?? 'payslip';
$uid    = $_GET['user_id'] ?? 'all';
$p_type = $_GET['period_type'] ?? 'monthly';
$month  = $_GET['month'] ?? '';
$year   = $_GET['year'] ?? '';

// ডাটা নিয়ে আসা
if ($type == 'payslip') {
    $sql = "SELECT p.*, u.name, u.designation 
            FROM payslips p 
            JOIN users u ON p.user_id = u.id 
            WHERE 1=1";
            
    if ($uid !== 'all') $sql .= " AND p.user_id = '$uid'";
    if ($p_type == 'monthly') $sql .= " AND p.month = '$month' AND p.year = '$year'";
    else $sql .= " AND p.year = '$year'";
    
    $sql .= " ORDER BY u.name ASC, FIELD(p.month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')";
} else {
    $sql = "SELECT a.*, u.name, u.designation 
            FROM attendance a 
            JOIN users u ON a.user_id = u.id 
            WHERE 1=1";
            
    if ($uid !== 'all') $sql .= " AND a.user_id = '$uid'";
    
    $m_num = date('m', strtotime($month));
    if ($p_type == 'monthly') $sql .= " AND MONTH(a.attendance_date) = '$m_num' AND YEAR(a.attendance_date) = '$year'";
    else $sql .= " AND YEAR(a.attendance_date) = '$year'";
    
    $sql .= " ORDER BY u.name ASC, a.attendance_date ASC";
}

$result = $conn->query($sql);
$all_rows = [];
while($row = $result->fetch_assoc()) { 
    $all_rows[] = $row; 
}
$user_info = $all_rows[0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll_Preview_FullWidth</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #fff;
            margin: 0;
            padding: 10px;
            color: #333;
        }

        .sheet {
            width: 98%;
            margin: 0 auto;
            padding: 10px;
        }

        .btn-print {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
            font-weight: bold;
        }
        
        .header-box {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header-box h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        /* New Info Section Styling */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #ccc;
            padding: 8px;
            background: #f9f9f9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th {
            background: #95a5a6;
            color: white;
            padding: 4px 2px;
            font-size: 10px;
            border: 1px solid #7f8c8d;
            text-align: center;
        }

        td {
            padding: 4px 2px;
            border: 1px solid #ccc;
            font-size: 10.5px;
            text-align: center;
        }
        
        .total-row {
            background: #eee !important;
            font-weight: bold;
        }

        .footer-sigs {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            border-top: 1px solid #000;
            width: 150px;
            text-align: center;
            font-size: 11px;
            padding-top: 5px;
        }

        @media print { 
            @page {
                size: landscape;
                margin: 0.5cm;
            }
            .btn-print {
                display: none;
            } 
            .sheet {
                width: 100%;
                padding: 0;
            }
            th {
                background: #95a5a6 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Print Report</button>

<div class="sheet">
    <div class="header-box">
        <h1>Payroll Detailed Report - <?= ($uid == 'all') ? 'All Staff' : 'Individual' ?></h1>
        <p style="margin:2px; font-size: 12px;">
            Period: <?= ($p_type == 'monthly') ? "$month $year" : "Year $year" ?>
        </p>
    </div>

    <?php if($user_info): ?>
        
        <?php if($uid !== 'all'): ?>
        <div class="info-section">
            <div>Name (ID): <?= htmlspecialchars($user_info['name']) ?> (<?= $user_info['user_id'] ?>)</div>
            <div>Designation: <?= htmlspecialchars($user_info['designation']) ?></div>
        </div>
        <?php endif; ?>

    <table>
        <thead>
            <?php if($type == 'payslip'): ?>
                <tr>
                    <th rowspan="2">ID-Name</th>
                    <th rowspan="2">Period</th>
                    <th colspan="8" style="background: #2c3e50;">Earnings (যোগফল)</th>
                    <th colspan="5" style="background: #c0392b;">Deductions (কর্তন)</th>
                    <th rowspan="2" style="background: #27ae60;">Gross</th>
                    <th rowspan="2" style="background: #27ae60;">Net</th>
                </tr>
                <tr>
                    <th>Basic</th><th>Med</th><th>Rent</th><th>Trn</th><th>Oth</th><th>OT</th><th>Bon</th><th>Extra</th>
                    <th>Abs</th><th>Adv</th><th>Loan</th><th>Oth</th><th>Total</th>
                </tr>
            <?php else: ?>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Status</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php 
            $total_all_net = 0; 
            foreach($all_rows as $row): 
                if($type == 'payslip'): 
                    $total_all_net += $row['net_salary'];
            ?>
                <tr>
                    <td style="text-align:left; white-space: nowrap;"><?= $row['user_id'] ?>-<?= htmlspecialchars($row['name']) ?></td>
                    <td style="white-space: nowrap;"><?= $row['month'] ?>-<?= $row['year'] ?></td>
                    <td><?= number_format($row['basic_salary'], 0) ?></td>
                    <td><?= number_format($row['medical_allowance'], 0) ?></td>
                    <td><?= number_format($row['house_rent'], 0) ?></td>
                    <td><?= number_format($row['transport_allowance'], 0) ?></td>
                    <td><?= number_format($row['other_allowance'], 0) ?></td>
                    <td><?= number_format($row['overtime_amount'], 0) ?></td>
                    <td><?= number_format($row['bonus'], 0) ?></td>
                    <td><?= number_format($row['other_earnings'], 0) ?></td>
                    <td><?= number_format($row['absent_deduction'], 0) ?></td>
                    <td><?= number_format($row['salary_advance_deduction'], 0) ?></td>
                    <td><?= number_format($row['home_loan_deduction'], 0) ?></td>
                    <td><?= number_format($row['other_deductions'], 0) ?></td>
                    <td style="color:red;"><?= number_format($row['total_deduction'], 0) ?></td>
                    <td><?= number_format($row['gross_salary'], 0) ?></td>
                    <td style="font-weight:bold; background:#f0fff0;"><?= number_format($row['net_salary'], 0) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td><?= $row['user_id'] ?></td>
                    <td style="text-align:left;"><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= date('d-M-y', strtotime($row['attendance_date'])) ?></td>
                    <td><?= $row['check_in'] ?></td>
                    <td><?= $row['check_out'] ?></td>
                    <td><?= $row['status'] ?></td>
                </tr>
            <?php endif; endforeach; ?>
        </tbody>
        <?php if($type == 'payslip'): ?>
        <tfoot>
            <tr class="total-row">
                <td colspan="16" style="text-align:right;">Grand Total:</td>
                <td style="background:#27ae60; color:white;"><?= number_format($total_all_net, 2) ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <div class="footer-sigs">
        <div class="sig-box">Prepared By</div>
        <div class="sig-box">Authorized Signature</div>
    </div>
    <?php else: ?>
        <h2 style="text-align:center;">No Records Found</h2>
    <?php endif; ?>
</div>

</body>
</html>