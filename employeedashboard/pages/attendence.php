<?php 
session_start();

// --- LOGIC UPDATE: Get ID from your auth_user session ---
if (isset($_SESSION['auth_user']['id'])) {
    $user_id = $_SESSION['auth_user']['id'];
} else {
    // Login kora na thakle root index.php (login) e pathabe
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['att_date'];
    $in_time = $_POST['in_time'];
    $out_time = $_POST['out_time'];

    if (!empty($date) && !empty($in_time) && !empty($out_time)) {
        
        // --- DUPLICATE CHECK LOGIC ---
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND attendance_date = ?");
        $check_stmt->bind_param("is", $user_id, $date);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Jodi record thake, tobe error message
            $_SESSION['status'] = "warning";
            $_SESSION['msg'] = "Attendance already submitted for this date!";
        } else {
            // Jodi record na thake, tobe insert hobe
            $start = strtotime($in_time);
            $end = strtotime($out_time);
            $seconds = $end - $start;
            $hours = round($seconds / 3600, 2);

            $stmt = $conn->prepare("INSERT INTO attendance (user_id, attendance_date, check_in, check_out, total_hours, status) VALUES (?, ?, ?, ?, ?, 'Present')");
            $stmt->bind_param("isssd", $user_id, $date, $in_time, $out_time, $hours);
            
            if ($stmt->execute()) {
                $_SESSION['status'] = "success";
                $_SESSION['msg'] = "Recorded: $hours Hours.";
            } else {
                $_SESSION['status'] = "error";
                $_SESSION['msg'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

include '../includes/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">Submit Attendance</h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card card-dark">
                    <div class="card-body">
                        <h4 class="card-title">Quick Attendance Entry (ID: <?php echo $user_id; ?>)</h4>
                        <form class="forms-sample" method="POST">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Attendance Date</label>
                                        <input type="date" class="form-control custom-input" name="att_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Check-In Time</label>
                                        <input type="time" class="form-control custom-input" name="in_time" value="09:00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Check-Out Time</label>
                                        <input type="time" class="form-control custom-input" name="out_time" value="18:00" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-success btn-lg mr-2">Submit Record</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <?php include '../includes/footer.php'; ?>
</div>

<?php if(isset($_SESSION['status'])): ?>
<script>
    Swal.fire({
        title: '<?php 
            if($_SESSION['status'] == "success") echo "Success!";
            elseif($_SESSION['status'] == "warning") echo "Oops!";
            else echo "Error!";
        ?>',
        text: '<?php echo $_SESSION['msg']; ?>',
        icon: '<?php echo $_SESSION['status']; ?>',
        width: '350px',
        confirmButtonColor: '<?php echo ($_SESSION['status'] == "success") ? "#00d25b" : "#ffab00"; ?>',
        background: '#191c24',
        color: '#fff'
    });
</script>
<?php 
    unset($_SESSION['status']);
    unset($_SESSION['msg']);
endif; 
?>

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
   width: 100%; }
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
   padding: 10px 30px;
   }
.btn-dark {
   background-color: #2a3038;
   border: 1px solid #2c2e33;
   padding: 10px 30px;
   }
.btn-lg {
   font-size: 0.94rem;
   }
</style>