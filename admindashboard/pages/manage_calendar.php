<?php 
session_start();
require_once __DIR__ . '/../../includes/admin_auth.php'; 


$host = 'localhost'; 
$db   = 'payroll'; 
$user = 'root'; 
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}


if (isset($_POST['set_holiday_btn'])) {
    $h_date = $_POST['h_date'];
    $h_name = $conn->real_escape_string($_POST['h_name']);
    
    $stmt = $conn->prepare("INSERT INTO holidays (holiday_date, holiday_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE holiday_name = ?");
    $stmt->bind_param("sss", $h_date, $h_name, $h_name);
    
    if ($stmt->execute()) {
        $_SESSION['status'] = "success";
        $_SESSION['msg'] = "Holiday updated successfully!";
    } else {
        $_SESSION['status'] = "error";
        $_SESSION['msg'] = "Something went wrong!";
    }
    
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?month=" . date('m', strtotime($h_date))); 
    exit();
}


$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = date('Y');


$holiday_res = $conn->query("SELECT * FROM holidays");
$admin_holidays = [];
while($h = $holiday_res->fetch_assoc()) {
    $admin_holidays[$h['holiday_date']] = $h['holiday_name'];
}

include '../includes/header.php'; 
?>

<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header mb-4">
            <h3 class="page-title text-white">
                <i class="mdi mdi-calendar-check text-primary mr-2"></i>
                Attendance Calendar - <?php echo date('F, Y', mktime(0, 0, 0, $selected_month, 10)); ?>
            </h3>
        </div>

        <div class="row">
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card" style="background: #191c24; border: 1px solid #2c2e33; border-radius: 10px;">
                    <div class="card-body">
                        <h4 class="card-title text-white mb-4">Declare Holiday</h4>
                        <form method="POST">
                            <div class="form-group mb-3">
                                <label class="text-muted">Choose Date</label>
                                <input type="date" name="h_date" class="form-control custom-input" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="text-muted">Holiday Reason</label>
                                <input type="text" name="h_name" class="form-control custom-input" placeholder="e.g. Eid, National Holiday" required>
                            </div>
                            <button type="submit" name="set_holiday_btn" class="btn btn-primary btn-block py-2" style="border-radius: 5px; font-weight: bold;">
                                <i class="mdi mdi-content-save mr-1"></i> Save Holiday
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8 grid-margin stretch-card">
                <div class="card" style="background: #191c24; border: 1px solid #2c2e33; border-radius: 10px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title text-white mb-0">Monthly View</h4>
                            <form method="GET" id="monthFilterForm">
                                <select name="month" class="form-control text-white select-month" onchange="document.getElementById('monthFilterForm').submit();">
                                    <?php 
                                    for ($m = 1; $m <= 12; $m++) {
                                        $month_val = str_pad($m, 2, '0', STR_PAD_LEFT);
                                        $month_name = date('F', mktime(0, 0, 0, $m, 10));
                                        $selected = ($selected_month == $month_val) ? 'selected' : '';
                                        echo "<option value='$month_val' $selected>$month_name</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>

                        <div class="table-responsive calendar-container">
                            <table class="table table-dark text-center">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $num_days = cal_days_in_month(CAL_GREGORIAN, $selected_month, $year);

                                    for ($d = 1; $d <= $num_days; $d++):
                                        $f_date = sprintf("%04d-%02d-%02d", $year, $selected_month, $d);
                                        $day_name = date('l', strtotime($f_date));
                                        
                                        $is_weekend = ($day_name == 'Friday' || $day_name == 'Saturday');
                                        $is_admin_h = isset($admin_holidays[$f_date]);

                                        if ($is_weekend || $is_admin_h) {
                                            $status = "HOLIDAY";
                                            $badge_class = "badge-danger";
                                            $remarks = $is_admin_h ? $admin_holidays[$f_date] : "Weekly Holiday";
                                            $row_bg = "background: rgba(252, 66, 74, 0.08);";
                                        } else {
                                            $status = "WORKING";
                                            $badge_class = "badge-outline-success";
                                            $remarks = "General Working Day";
                                            $row_bg = "";
                                        }
                                    ?>
                                        <tr style="<?php echo $row_bg; ?>">
                                            <td><?php echo date('d M', strtotime($f_date)); ?></td>
                                            <td class="text-muted"><?php echo $day_name; ?></td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>" style="min-width: 80px;">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo $remarks; ?></td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-input {
            background: #2a3038 !important;
            border: 1px solid #333 !important;
            color: #fff !important;
            padding: 12px !important;
            border-radius: 5px !important;
        }

        .select-month {
            background: #2a3038 !important;
            border: 1px solid #333 !important;
            border-radius: 5px;
            cursor: pointer;
            width: 150px;
        }

        .calendar-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #2c2e33;
            border-radius: 5px;
        }

        .calendar-container thead th {
            background: #2a3038;
            position: sticky;
            top: 0;
            z-index: 10;
            border-top: none;
            padding: 15px;
        }

        .badge-danger {
            background: #fc424a;
            color: #fff;
        }

        .badge-outline-success {
            border: 1px solid #00d25b;
            color: #00d25b;
            background: transparent;
        }

        
        .calendar-container::-webkit-scrollbar {
            width: 6px;
        }
        .calendar-container::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 10px;
        }
        .calendar-container::-webkit-scrollbar-track {
            background: #191c24;
        }
    </style>

    <?php if(isset($_SESSION['status'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: '<?php echo ($_SESSION['status'] == "success") ? "Success!" : "Error!"; ?>',
            text: '<?php echo $_SESSION['msg']; ?>',
            icon: '<?php echo $_SESSION['status']; ?>',
            background: '#191c24',
            color: '#fff',
            confirmButtonColor: '#00d25b'
        });
    </script>
    <?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>

    <?php include '../includes/footer.php'; ?>
</div>