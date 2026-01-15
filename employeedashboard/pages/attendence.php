<?php 
session_start();

// --- LOGIC UPDATE: Get ID from your auth_user session ---
if (isset($_SESSION['auth_user']['id'])) {
    $user_id = $_SESSION['auth_user']['id'];
} else {
    header("Location: ../../index.php");
    exit();
}

// Database Connection
$host = 'localhost';
$db   = 'payroll';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// --- 🟢 FORM LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. CHECK-IN LOGIC
    if (isset($_POST['check_in_btn'])) {
        $date = $_POST['att_date'];
        $in_time = $_POST['in_time'];

        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND attendance_date = ?");
        $check_stmt->bind_param("is", $user_id, $date);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['status'] = "warning";
            $_SESSION['msg'] = "Attendance already started for this date!";
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (user_id, attendance_date, check_in, status) VALUES (?, ?, ?, 'Present')");
            $stmt->bind_param("iss", $user_id, $date, $in_time);
            if ($stmt->execute()) {
                $_SESSION['status'] = "success";
                $_SESSION['msg'] = "Check-In Recorded!";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }

    // 2. CHECK-OUT LOGIC
    if (isset($_POST['check_out_btn'])) {
        $att_id = $_POST['att_id'];
        $out_time = $_POST['out_time'];

        $res = $conn->query("SELECT check_in FROM attendance WHERE id = '$att_id'");
        $row = $res->fetch_assoc();
        $in_time = $row['check_in'];

        $start = strtotime($in_time);
        $end = strtotime($out_time);
        $seconds = $end - $start;
        $hours = round($seconds / 3600, 2);

        $stmt = $conn->prepare("UPDATE attendance SET check_out = ?, total_hours = ? WHERE id = ?");
        $stmt->bind_param("sdi", $out_time, $hours, $att_id);
        
        if ($stmt->execute()) {
            $_SESSION['status'] = "success";
            $_SESSION['msg'] = "Check-Out Done! Total: $hours Hours.";
        }
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Active session khuje ber kora
$pending_query = $conn->query("SELECT * FROM attendance WHERE user_id = '$user_id' AND check_out IS NULL ORDER BY id DESC LIMIT 1");
$pending_att = $pending_query->fetch_assoc();

include '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">Submit Attendance</h3>
        </div>

        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-dark">
                    <div class="card-body">
                        <h4 class="card-title">Check-In (ID: <?php echo $user_id; ?>)</h4>
                        <form class="forms-sample" method="POST">
                            <div class="form-group">
                                <label>Attendance Date</label>
                                <input type="date" class="form-control custom-input" name="att_date" value="" required>
                            </div>
                            <div class="form-group">
                                <label>Check-In Time</label>
                                <input type="time" class="form-control custom-input" name="in_time" value="" required>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="check_in_btn" class="btn btn-success btn-lg w-100" style="border-radius: 50px;">Submit Check-In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-dark">
                    <div class="card-body">
                        <h4 class="card-title">Check-Out</h4>
                        <?php if ($pending_att): ?>
                            <form class="forms-sample" method="POST">
                                <input type="hidden" name="att_id" value="<?php echo $pending_att['id']; ?>">
                                <div class="form-group">
                                    <label>Active In-Time Info</label>
                                    <input type="text" class="form-control custom-input" value="Started: <?php echo $pending_att['check_in']; ?> (<?php echo $pending_att['attendance_date']; ?>)" readonly style="opacity: 0.8; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>Check-Out Time</label>
                                    <input type="time" class="form-control custom-input" name="out_time" value="" required>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="check_out_btn" class="btn btn-success btn-lg w-100" style="border-radius: 50px;">Submit Check-Out</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="mdi mdi-clock-check-outline" style="font-size: 50px; color: #2c2e33;"></i>
                                <p class="text-muted mt-2">No active session found. <br> Please check-in first.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <?php include '../includes/footer.php'; ?>
</div>

<style>
.main-panel {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
 }
.content-wrapper {
    flex: 1;
    background: #000000;
    padding: 2.125rem 2.5rem;
 }
.page-title {
    color: #ffffff;
    font-size: 1.125rem;
    font-weight: 500;
 }
.card-dark {
    background: #191c24;
    border: none;
    border-radius: 5px;
    width: 100%;
 }
.custom-input {
    background: #2a3038 !important;
    border: 1px solid #2c2e33 !important;
    color: #ffffff !important;
    padding: 0.875rem 1.1rem;
    height: auto;
 }
label {
    color: #ffffff;
    font-size: 0.875rem;
    margin-bottom: 0.8rem;
    display: block;
 }
.btn-success {
    background-color: #00d25b;
    border-color: #00d25b;
    padding: 12px 30px;
    font-weight: bold;
 }
.btn-lg {
    font-size: 0.94rem;
 }
</style>

<?php if(isset($_SESSION['status'])): ?>
<script>
    Swal.fire({
        title: '<?php echo ($_SESSION['status'] == "success") ? "Success!" : "Wait!"; ?>',
        text: '<?php echo $_SESSION['msg']; ?>',
        icon: '<?php echo $_SESSION['status']; ?>',
        width: '350px',
        confirmButtonColor: '#00d25b',
        background: '#191c24', color: '#fff'
    });
</script>
<?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>