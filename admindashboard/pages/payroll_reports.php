<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; 
$db   = 'payroll'; 
$user = 'root'; 
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

include '../includes/header.php';


$selected_month = $_GET['month'] ?? date('F');
$selected_year = $_GET['year'] ?? date('Y');


$query = "SELECT p.*, u.name as emp_name 
          FROM payslips p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.month = '$selected_month' AND p.year = '$selected_year'
          ORDER BY p.id DESC";
$report_data = $conn->query($query);

$total_payout = 0;
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2 class="text-white mb-0">Payroll Report Summary</h2>
            <button type="button" onclick="window.print()" class="btn-print-action">
                <i class="mdi mdi-printer mr-1"></i> Print PDF Report
            </button>
        </div>

        <div class="filter-container mb-4 no-print">
            <form method="GET" class="report-filter-form">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="text-muted small">Select Month</label>
                        <select name="month" class="form-control custom-select-report">
                            <?php 
                            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                            foreach($months as $m) {
                                $selected = ($m == $selected_month) ? "selected" : "";
                                echo "<option value='$m' $selected>$m</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Select Year</label>
                        <select name="year" class="form-control custom-select-report">
                            <?php 
                            for($y = 2024; $y <= 2030; $y++) {
                                $selected = ($y == $selected_year) ? "selected" : "";
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn-filter">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>

        <div id="printableReport" style="overflow-x:auto;">
            <div class="print-header text-center mb-4" style="display:none;">
                <h2 style="color: #000; margin-bottom: 5px;">Payroll Report</h2>
                <p style="color: #555;">Month: <?php echo $selected_month . " " . $selected_year; ?></p>
            </div>

            <table class="employee-table">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Employee Name</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Paid</th>
                        <th>Status</th>
                        <th>Pay Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($report_data->num_rows > 0): $sl = 1; ?>
                    <?php while($row = $report_data->fetch_assoc()): 
                        $total_payout += $row['net_salary'];
                    ?>
                    <tr>
                        <td><?php echo $sl++; ?></td>
                        <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td><?php echo number_format($row['gross_salary'], 2); ?></td>
                        <td style="color:#ff4d4f">- <?php echo number_format($row['total_deduction'], 2); ?></td>
                        <td style="color:#4BB543; font-weight:bold;"><?php echo number_format($row['net_salary'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo ($row['payment_status'] == 'Paid') ? 'badge-paid' : 'badge-pending'; ?>">
                                <?php echo $row['payment_status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d-M-Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right; font-weight:bold;">Total Net Salary Distribution:</td>
                        <td colspan="3" style="color:#4BB543; font-weight:bold; font-size:16px;">
                            BDT <?php echo number_format($total_payout, 2); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 30px;">
                            No data found for <?php echo $selected_month . " " . $selected_year; ?>.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
     
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #191C24;
            color: #fff;
        }

        .employee-table th, 
        .employee-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        .employee-table th {
            background-color: #2A2E39;
            font-weight: bold;
            color: #fff !important;
        }

        .employee-table tr:hover {
            background-color: #2e3340;
        }

        .total-row {
            background: #2A2E39 !important;
        }

        
        .report-filter-form {
            background: #191C24;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #2c2e33;
        }

        .custom-select-report {
            background: #2A3038 !important;
            border: 1px solid #444 !important;
            color: #fff !important;
            height: 45px;
        }

        .btn-filter {
            background: #4BB543;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            height: 45px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-filter:hover {
            background: #3e9e37;
        }

        .btn-print-action {
            background: #0090e7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

      
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-paid {
            background: rgba(75, 181, 67, 0.15);
            color: #4BB543;
            border: 1px solid #4BB543;
        }

        .badge-pending {
            background: rgba(255, 171, 0, 0.15);
            color: #ffab00;
            border: 1px solid #ffab00;
        }

        
        @media print {
            .no-print, 
            .sidebar, 
            .navbar, 
            .footer,
            .btn-filter {
                display: none !important;
            }

            .main-panel {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .content-wrapper {
                background: #fff !important;
                padding: 0 !important;
            }

            .print-header {
                display: block !important;
            }

            .employee-table {
                color: #000 !important;
                background: #fff !important;
            }

            .employee-table th {
                background: #f2f2f2 !important;
                color: #000 !important;
                border: 1px solid #ddd !important;
            }

            .employee-table td {
                border: 1px solid #ddd !important;
                color: #000 !important;
            }
            
            .total-row {
                background: #f9f9f9 !important;
            }
        }
    </style>

    <?php include '../includes/footer.php'; ?>
</div>