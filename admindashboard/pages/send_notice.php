<?php 
session_start();

// --- Database Connection ---
$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// --- 🟢 NOTICE LOGIC: Notice Send Kora ---
if (isset($_POST['send_notice_btn'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO notices (title, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $message);
    
    if ($stmt->execute()) {
        $_SESSION['status'] = "success";
        $_SESSION['msg'] = "Notice broadcasted successfully!";
    } else {
        $_SESSION['status'] = "error";
        $_SESSION['msg'] = "Something went wrong!";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

include '../includes/header.php'; 
?>



<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header">
            <h3 class="page-title text-white">Broadcast Announcement</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 grid-margin stretch-card">
                <div class="card card-notice">
                    <div class="card-body p-5">
                        <h4 class="card-title text-white mb-4">Send New Notice to All Employees</h4>
                        
                        <form method="POST" class="forms-sample">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><i class="mdi mdi-format-title mr-1"></i> Notice Title</label>
                                        <input type="text" name="title" class="form-control custom-input" placeholder="e.g. Eid Vacation Notice" required>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label><i class="mdi mdi-message-text-outline mr-1"></i> Detailed Message</label>
                                        <textarea name="message" class="form-control custom-input" rows="1" placeholder="Type your announcement details here..." required style="min-height: 50px;"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12 text-right">
                                    <button type="reset" class="btn btn-dark mr-2" style="border-radius: 8px; padding: 12px 25px;">Clear</button>
                                    <button type="submit" name="send_notice_btn" class="btn btn-broadcast">
                                        <i class="mdi mdi-send mr-1"></i> Broadcast to Employees
                                    </button>
                                </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: '<?php echo ($_SESSION['status'] == "success") ? "Success!" : "Wait!"; ?>',
        text: '<?php echo $_SESSION['msg']; ?>',
        icon: '<?php echo $_SESSION['status']; ?>',
        background: '#191c24', 
        color: '#fff', 
        confirmButtonColor: '#00d25b'
    });
</script>
<?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>

<style>
    .content-wrapper { 
        background: #000 !important; 
        min-height: 100vh;
    }
    .card-notice { 
        background: #191c24; 
        border: 1px solid #2c2e33; 
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }
    .custom-input { 
        background: #2a3038 !important; 
        border: 1px solid #2c2e33 !important; 
        color: #fff !important; 
        padding: 15px;
        border-radius: 8px;
    }
    .custom-input:focus {
        border-color: #00d25b !important;
        box-shadow: none;
    }
    .btn-broadcast { 
        border-radius: 8px; 
        font-weight: bold;
        padding: 12px 30px;
        background: linear-gradient(45deg, #0090e7, #00d25b);
        border: none;
        color: white;
        transition: 0.3s;
    }
    .btn-broadcast:hover {
        transform: translateY(-2px);
        opacity: 0.9;
    }
    label {
        font-weight: 500;
        margin-bottom: 8px;
        color: #abb2b9;
    }
</style>