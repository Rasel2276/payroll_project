<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; 
$db   = 'payroll'; 
$user = 'root'; 
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

include '../includes/header.php';

$report_type     = $_GET['report_type'] ?? 'payslip';
$employee_filter = $_GET['employee_filter'] ?? 'all';
$selected_user   = $_GET['user_id'] ?? '';
$period_type     = $_GET['period_type'] ?? 'monthly';
$selected_month  = $_GET['month'] ?? date('F');
$selected_year   = $_GET['year'] ?? date('Y');

$form_submitted = isset($_GET['generate_report']);
$employees = $conn->query("SELECT id, name FROM users WHERE role='employee' ORDER BY name ASC");

$query = "";
if ($form_submitted) {
    if ($report_type == 'payslip') {
        $query = "SELECT p.*, u.name as emp_name, u.designation FROM payslips p JOIN users u ON p.user_id = u.id WHERE 1=1";
        if ($employee_filter == 'individual' && $selected_user) $query .= " AND p.user_id = '$selected_user'";
        if ($period_type == 'monthly') $query .= " AND p.month = '$selected_month' AND p.year = '$selected_year'";
        else $query .= " AND p.year = '$selected_year'";
    } 
    else {
        $query = "SELECT a.*, u.name as emp_name, u.designation FROM attendance a JOIN users u ON a.user_id = u.id WHERE 1=1";
        if ($report_type == 'absent') $query .= " AND a.status = 'Absent'";
        if ($employee_filter == 'individual' && $selected_user) $query .= " AND a.user_id = '$selected_user'";
        
        if ($period_type == 'monthly') {
            $month_num = date('m', strtotime($selected_month));
            $query .= " AND MONTH(a.attendance_date) = '$month_num' AND YEAR(a.attendance_date) = '$selected_year'";
        } else { 
            $query .= " AND YEAR(a.attendance_date) = '$selected_year'"; 
        }
    }
}
$result = ($query != "") ? $conn->query($query) : null;
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header no-print">
            <h3 class="page-title text-white">Payroll & Attendance Report</h3>
        </div>

        <div class="card card-dark mb-4 no-print">
            <div class="card-body">
                <form method="GET" id="reportForm">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Report Category</label>
                            <select name="report_type" class="form-control custom-input">
                                <option value="payslip" <?= $report_type=='payslip'?'selected':'' ?>>Salary Report</option>
                                <option value="attendance" <?= $report_type=='attendance'?'selected':'' ?>>Attendance Report</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Employee Selection</label>
                            <select name="employee_filter" class="form-control custom-input" onchange="this.form.submit()">
                                <option value="all" <?= $employee_filter=='all'?'selected':'' ?>>All Employees</option>
                                <option value="individual" <?= $employee_filter=='individual'?'selected':'' ?>>Individual</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Time Period</label>
                            <select name="period_type" class="form-control custom-input" onchange="this.form.submit()">
                                <option value="monthly" <?= $period_type=='monthly'?'selected':'' ?>>Monthly</option>
                                <option value="yearly" <?= $period_type=='yearly'?'selected':'' ?>>Full Year</option>
                            </select>
                        </div>

                        <?php if($period_type == 'monthly'): ?>
                        <div class="col-md-3 mb-3">
                            <label>Month</label>
                            <select name="month" class="form-control custom-input">
                                <?php foreach(["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"] as $m): ?>
                                    <option value="<?= $m ?>" <?= $selected_month==$m?'selected':'' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row align-items-end">
                        <?php if($employee_filter == 'individual'): ?>
                        <div class="col-md-3 mb-3">
                            <label>Select Name & ID</label>
                            <select name="user_id" class="form-control custom-input">
                                <option value="">-- Choose Employee --</option>
                                <?php while($emp = $employees->fetch_assoc()): ?>
                                    <option value="<?= $emp['id'] ?>" <?= $selected_user==$emp['id']?'selected':'' ?>>ID: <?= $emp['id'] ?> | <?= htmlspecialchars($emp['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-2 mb-3">
                            <label>Year</label>
                            <select name="year" class="form-control custom-input">
                                <?php for($y=2024; $y<=2030; $y++): ?>
                                    <option value="<?= $y ?>" <?= $selected_year==$y?'selected':'' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" name="generate_report" value="1" class="btn btn-success font-weight-bold" style="min-width: 150px;">
                                    GENERATE
                                </button>
                                
                                <?php if($form_submitted && $employee_filter == 'all'): ?>
                                    <a href="preview_excel.php?report_type=<?= $report_type ?>&user_id=all&period_type=<?= $period_type ?>&month=<?= $selected_month ?>&year=<?= $selected_year ?>" target="_blank" class="btn btn-warning font-weight-bold">
                                        <i class="mdi mdi-printer"></i> PREVIEW ALL
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-dark">
            <div class="card-body">
                <?php if($form_submitted): ?>
                    <div class="table-responsive">
                        <table class="employee-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Month/Year</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): 
                                        $preview_url = "preview_excel.php?report_type=$report_type&user_id=".$row['user_id']."&period_type=$period_type&month=$selected_month&year=$selected_year";
                                    ?>
                                        <tr>
                                            <td><?= $row['user_id'] ?></td>
                                            <td><?= htmlspecialchars($row['emp_name'] ?? $row['name']) ?></td>
                                            <td><?= htmlspecialchars($row['designation'] ?? 'N/A') ?></td>
                                            <td><?= ($report_type == 'payslip') ? $row['month']."-".$row['year'] : $selected_month." ".$selected_year; ?></td>
                                            <td>
                                                <a href="<?= $preview_url ?>" target="_blank" class="btn btn-info btn-sm">
                                                    <i class="mdi mdi-eye"></i> Preview
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">No Records Found!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>

<style>
    .card-dark {
        background: #191c24;
        border: 1px solid #2c2e33;
        border-radius: 8px;
    }
    
    .custom-input {
        background: #2a3038 !important;
        color: white !important;
        border: 1px solid #2c2e33 !important;
        padding: 10px;
    }
    
    .employee-table {
        width: 100%;
        border-collapse: collapse;
        color: #fff;
    }
    
    .employee-table th {
        background: #2A2E39;
        padding: 15px;
        border-bottom: 2px solid #444;
        color: #adb5bd;
        text-transform: uppercase;
        font-size: 12px;
    }
    
    .employee-table td {
        padding: 15px;
        border-bottom: 1px solid #2c2e33;
        font-size: 14px;
    }
</style>