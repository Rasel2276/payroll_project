<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

include '../includes/header.php';

// --- ১. ফিল্টার লজিক ---
$selected_month = $_GET['month'] ?? date('F');
$selected_year = $_GET['year'] ?? date('Y');

// ডাটা ফেচ করা
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
        <h2 class="text-white mb-4">Payroll Report Summary</h2>

        <div class="filter-container mb-4">
            <form method="GET" class="employee-form" style="padding: 15px;">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <button type="submit" class="btn-filter">Generate Report</button>
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" onclick="window.print()" class="btn-print">Print PDF</button>
                    </div>
                </div>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th style="color: #fff !important;">SL</th>
                        <th style="color: #fff !important;">Employee Name</th>
                        <th style="color: #fff !important;">Gross Pay</th>
                        <th style="color: #fff !important;">Deductions</th>
                        <th style="color: #fff !important;">Net Paid</th>
                        <th style="color: #fff !important;">Status</th>
                        <th style="color: #fff !important;">Pay Date</th>
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
                    <tr style="background: #2A2E39;">
                        <td colspan="4" style="text-align:right; font-weight:bold; color:#fff;">Total Net Salary Distribution:</td>
                        <td colspan="3" style="color:#4BB543; font-weight:bold; font-size:16px;">
                            BDT <?php echo number_format($total_payout, 2); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">No data found for <?php echo $selected_month . " " . $selected_year; ?>.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        /* আপনার ডিজাইন গাইডলাইন ফলো করা হয়েছে */
        .employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
        .employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
        .employee-table th { background-color: #2A2E39; font-weight: bold; color: #fff !important; }
        .employee-table tr:hover { background-color: #2e3340; }

        /* ফিল্টার ও বাটন স্টাইল */
        .custom-select-report { background: #2A3038 !important; border: 1px solid #555 !important; color: #fff !important; }
        .btn-filter { background: #4BB543; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold; }
        .btn-print { background: #0090e7; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold; }

        /* ব্যাজ স্টাইল */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: rgba(75, 181, 67, 0.2); color: #4BB543; }
        .badge-pending { background: rgba(255, 171, 0, 0.2); color: #ffab00; }

        /* প্রিন্ট করার সময় শুধু টেবিল দেখাবে */
        @media print {
            .btn-filter, .btn-print, .filter-container, .sidebar, .navbar { display: none !important; }
            .main-panel { width: 100% !important; margin: 0; padding: 0; }
            .employee-table { color: #000; background: #fff; }
            .employee-table th { background: #eee !important; color: #000 !important; }
            .employee-table td { border: 1px solid #ddd; color: #000 !important; }
        }
    </style>

    <?php include '../includes/footer.php'; ?>
</div>