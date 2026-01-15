<?php 
session_start();

// --- 1. Database Connection ---
$host = "localhost";
$user = "root";
$pass = "";
$db   = "payroll";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// --- 2. Auth Check ---
if (isset($_SESSION['auth_user']['id'])) {
    $user_id = $_SESSION['auth_user']['id'];
} else {
    header("Location: ../../index.php");
    exit();
}

// --- 3. Leave Balance Logic ---
$current_month = date('m');
$current_year  = date('Y');

$yearly_casual_limit = 10; 
$yearly_sick_limit   = 15;

// Monthly Taken
$m_casual = mysqli_query($conn, "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM leave_requests 
    WHERE user_id = '$user_id' AND leave_type = 'Casual' AND status != 'Rejected' 
    AND MONTH(start_date) = '$current_month' AND YEAR(start_date) = '$current_year'");
$monthly_casual_taken = mysqli_fetch_assoc($m_casual)['total'] ?? 0;

$m_sick = mysqli_query($conn, "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM leave_requests 
    WHERE user_id = '$user_id' AND leave_type = 'Sick' AND status != 'Rejected' 
    AND MONTH(start_date) = '$current_month' AND YEAR(start_date) = '$current_year'");
$monthly_sick_taken = mysqli_fetch_assoc($m_sick)['total'] ?? 0;

// Yearly Taken
$y_casual = mysqli_query($conn, "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM leave_requests 
    WHERE user_id = '$user_id' AND leave_type = 'Casual' AND status = 'Approved' 
    AND YEAR(start_date) = '$current_year'");
$yearly_casual_taken = mysqli_fetch_assoc($y_casual)['total'] ?? 0;

$y_sick = mysqli_query($conn, "SELECT SUM(DATEDIFF(end_date, start_date) + 1) as total FROM leave_requests 
    WHERE user_id = '$user_id' AND leave_type = 'Sick' AND status = 'Approved' 
    AND YEAR(start_date) = '$current_year'");
$yearly_sick_taken = mysqli_fetch_assoc($y_sick)['total'] ?? 0;

// Remaining Calculations
$m_casual_rem = 2 - $monthly_casual_taken;
$m_sick_rem   = 3 - $monthly_sick_taken;
$y_casual_rem = $yearly_casual_limit - $yearly_casual_taken;
$y_sick_rem   = $yearly_sick_limit - $yearly_sick_taken;

// --- 4. Form Submission Logic ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $reason     = mysqli_real_escape_string($conn, $_POST['reason']);

    $d1 = new DateTime($start_date);
    $d2 = new DateTime($end_date);
    $requested_days = $d1->diff($d2)->days + 1;

    $allow = true;
    $msg = "";

    if ($leave_type == 'Casual' && $requested_days > $m_casual_rem) {
        $allow = false;
        $msg = "Error: This month's Casual Leave limit exceeded. Remaining: $m_casual_rem days.";
    }

    if ($leave_type == 'Sick' && $requested_days > $m_sick_rem) {
        $allow = false;
        $msg = "Error: This month's Sick Leave limit exceeded. Remaining: $m_sick_rem days.";
    }

    if ($allow) {
        mysqli_query($conn, "INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, status) 
                            VALUES ('$user_id','$leave_type','$start_date','$end_date','$reason','Pending')");

        $_SESSION['msg'] = "Application submitted successfully!";
        $_SESSION['msg_class'] = "alert-success bg-success";
    } else {
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_class'] = "alert-danger bg-danger";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

include '../includes/header.php'; 
?>

<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header mb-4">
            <h3 class="page-title text-white">Leave Application</h3>
        </div>

        <div class="row">
            <div class="col-md-7 grid-margin stretch-card">
                <div class="card card-dark shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="card-title text-white mb-4">
                            Apply for New Leave (ID: <?php echo $user_id; ?>)
                        </h4>

                        <?php if(isset($_SESSION['msg'])): ?>
                            <div class="alert <?php echo $_SESSION['msg_class']; ?> text-white border-0 mb-4">
                                <?php 
                                    echo $_SESSION['msg']; 
                                    unset($_SESSION['msg'], $_SESSION['msg_class']); 
                                ?>
                            </div>
                        <?php endif; ?>

                        <form class="forms-sample" method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label>Select Leave Type</label>
                                        <select class="form-control custom-input" name="leave_type" required>
                                            <option value="" selected disabled>Choose Type</option>
                                            <option value="Casual">Casual Leave (Max 2 Days/Month)</option>
                                            <option value="Sick">Sick Leave (Max 3 Days/Month)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" class="form-control custom-input" name="start_date" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" class="form-control custom-input" name="end_date" required>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label>Reason for Leave</label>
                                        <textarea class="form-control custom-input" name="reason" rows="5" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success btn-lg mr-2 px-4 py-2">Submit Application</button>
                                <button type="reset" class="btn btn-dark btn-lg px-4 py-2 text-white">Clear Form</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5 grid-margin stretch-card">
                <div class="card card-dark shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-white mb-3" style="font-size: 1.1rem; padding-bottom: 15px; border-bottom: 1px solid #2c2e33;">
                            Remaining Leave Balance
                        </h4>

                        <div class="table-responsive">
                            <table class="table text-white" style="border: 1px solid #2c2e33;">
                                <thead>
                                    <tr style="background: #2a3038;">
                                        <th>Type</th>
                                        <th>Monthly Left</th>
                                        <th>Yearly Left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Casual</td>
                                        <td class="text-info"><?php echo max(0,$m_casual_rem); ?> Days</td>
                                        <td class="text-muted"><?php echo max(0,$y_casual_rem); ?> / <?php echo $yearly_casual_limit; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Sick</td>
                                        <td class="text-info"><?php echo max(0,$m_sick_rem); ?> Days</td>
                                        <td class="text-muted"><?php echo max(0,$y_sick_rem); ?> / <?php echo $yearly_sick_limit; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</div>

<style>
    .main-panel { min-height: 100vh; display: flex; flex-direction: column; }
    .content-wrapper { flex: 1; background: #000000 !important; padding: 2.125rem 2.5rem; }
    .page-title { color: #ffffff; font-size: 1.25rem; font-weight: 600; letter-spacing: 0.5px; }
    .card-dark { background: #191c24; border: 1px solid #2c2e33; border-radius: 8px; }
    .card-title { color: #ffffff; font-size: 1.1rem; border-bottom: 1px solid #2c2e33; padding-bottom: 15px; }
    .form-group { margin-bottom: 1rem; }
    label { color: #adb5bd; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.7rem; display: block; }
    .custom-input { background-color: #2a3038 !important; border: 1px solid #2c2e33 !important; color: #ffffff !important; padding: 12px 15px !important; height: auto !important; border-radius: 6px !important; font-size: 0.9rem; }
    .custom-input:focus { border-color: #00d25b !important; box-shadow: 0 0 0 0.2rem rgba(0, 210, 91, 0.15); outline: none; }
    .btn-success { background-color: #00d25b; border: none; font-size: 0.95rem; font-weight: 600; }
    .btn-dark { background-color: #2a3038; border: 1px solid #2c2e33; font-size: 0.95rem; font-weight: 600; }
    .table td, .table th { border-top: 1px solid #2c2e33; padding: 12px; }
    @media (max-width: 768px) { .content-wrapper { padding: 1.5rem 1rem; } }
</style>