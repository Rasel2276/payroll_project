<?php 
session_start();

// --- Database Connection ---
$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// --- 🟢 ADMIN LOGIC: Holiday Declare Kora ---
if (isset($_POST['set_holiday_btn'])) {
    $h_date = $_POST['h_date'];
    $h_name = $_POST['h_name'];
    
    $stmt = $conn->prepare("INSERT INTO holidays (holiday_date, holiday_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE holiday_name = ?");
    $stmt->bind_param("sss", $h_date, $h_name, $h_name);
    $stmt->execute();
    
    $_SESSION['status'] = "success";
    $_SESSION['msg'] = "Holiday updated successfully!";
    header("Location: " . $_SERVER['PHP_SELF'] . "?month=" . date('m', strtotime($h_date))); 
    exit();
}

// --- Filter Logic: Month select kora ---
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = date('Y');

// --- Holiday List Fetch Kora ---
$holiday_res = $conn->query("SELECT * FROM holidays");
$admin_holidays = [];
while($h = $holiday_res->fetch_assoc()) {
    $admin_holidays[$h['holiday_date']] = $h['holiday_name'];
}

include '../includes/header.php'; 
?>

<div class="main-panel">
    <div class="content-wrapper" style="background: #000;">
        
        <div class="page-header">
            <h3 class="page-title text-white">Monthly Attendance Calendar - <?php echo date('F', mktime(0, 0, 0, $selected_month, 10)); ?></h3>
        </div>

        <div class="row">
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card card-dark" style="background: #191c24; border: 1px solid #2c2e33;">
                    <div class="card-body">
                        <h4 class="card-title text-warning">Declare Holiday</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label class="text-white">Choose Date</label>
                                <input type="date" name="h_date" class="form-control text-white custom-input" required>
                            </div>
                            <div class="form-group">
                                <label class="text-white">Reason</label>
                                <input type="text" name="h_name" class="form-control text-white custom-input" placeholder="e.g. Eid, National Holiday" required>
                            </div>
                            <button type="submit" name="set_holiday_btn" class="btn btn-primary w-100" style="border-radius: 50px;">Save Holiday</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7 grid-margin stretch-card">
                <div class="card card-dark" style="background: #191c24; border: 1px solid #2c2e33;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title text-info mb-0">Calendar View</h4>
                            <form method="GET" id="monthFilterForm">
                                <select name="month" class="form-control text-white" style="background: #2a3038; border: 1px solid #2c2e33;" onchange="document.getElementById('monthFilterForm').submit();">
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

                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-dark table-hover text-center">
                                <thead style="background: #2a3038; position: sticky; top: 0; z-index: 1;">
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
                                            $badge = "badge-danger";
                                            $remarks = $is_admin_h ? $admin_holidays[$f_date] : "Weekly Holiday";
                                            $row_style = "background: rgba(255, 66, 74, 0.15);";
                                        } else {
                                            $status = "GENERAL DAY";
                                            $badge = "badge-outline-light";
                                            $remarks = "Working Day";
                                            $row_style = "";
                                        }
                                    ?>
                                        <tr style="<?php echo $row_style; ?>">
                                            <td><?php echo date('d M, Y', strtotime($f_date)); ?></td>
                                            <td><?php echo $day_name; ?></td>
                                            <td><span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span></td>
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
    <?php include '../includes/footer.php'; ?>
</div>

<style>
.custom-input { background: #2a3038 !important; border: 1px solid #2c2e33 !important; color: #fff !important; padding: 10px; margin-bottom: 10px; }
.badge-danger { background-color: #fc424a; color: #fff; }
.badge-outline-light { border: 1px solid #6c7293; color: #6c7293; background: transparent; }
/* Table Scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>

<?php if(isset($_SESSION['status'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: '<?php echo ($_SESSION['status'] == "success") ? "Success!" : "Wait!"; ?>',
        text: '<?php echo $_SESSION['msg']; ?>',
        icon: '<?php echo $_SESSION['status']; ?>',
        background: '#191c24', color: '#fff', confirmButtonColor: '#00d25b'
    });
</script>
<?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>