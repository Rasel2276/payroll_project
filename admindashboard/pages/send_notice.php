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

<style>
    .content-wrapper { 
        background: #000 !important; 
    }
    .page-title {
        color: #ffffff;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }
    .card-notice { 
        background: #191c24; 
        border: 1px solid #2c2e33; 
        border-radius: 8px;
    }
    .custom-input { 
        background: #2a3038 !important; 
        border: 1px solid #2c2e33 !important; 
        color: #fff !important; 
        padding: 12px;
    }
    .custom-input:focus {
        border-color: #00d25b !important;
        color: #fff;
    }
    .btn-rounded-custom { 
        border-radius: 50px; 
        font-weight: bold;
        padding: 12px;
        background-color: #0090e7;
        border: none;
    }
    .notice-scroll-area {
        max-height: 480px; 
        overflow-y: auto;
        padding-right: 5px;
    }
    .notice-card-item { 
        border-left: 4px solid #ffab00; 
        background: #2a3038; 
        margin-bottom: 15px; 
        padding: 20px;
        border-radius: 4px;
    }
    .notice-card-title {
        color: #ffab00;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .notice-card-body {
        color: #e4e4e4;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 10px;
    }
    .notice-card-footer {
        color: #6c7293;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
    }
    .notice-card-footer i {
        margin-right: 5px;
    }

    /* Scrollbar Style */
    .notice-scroll-area::-webkit-scrollbar { width: 4px; }
    .notice-scroll-area::-webkit-scrollbar-thumb { background: #444; border-radius: 10px; }
</style>

<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header">
            <h3 class="page-title">Employee Notice Board</h3>
        </div>

        <div class="row">
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card card-notice">
                    <div class="card-body">
                        <h4 class="card-title text-warning mb-4">Broadcast New Notice</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label class="text-white">Notice Title</label>
                                <input type="text" name="title" class="form-control custom-input" placeholder="e.g. Office Timing Change" required>
                            </div>
                            <div class="form-group">
                                <label class="text-white">Detailed Message</label>
                                <textarea name="message" class="form-control custom-input" rows="6" placeholder="Write the announcement details here..." required></textarea>
                            </div>
                            <button type="submit" name="send_notice_btn" class="btn btn-primary w-100 btn-rounded-custom">
                                <i class="mdi mdi-send"></i> Send Notice to All
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7 grid-margin stretch-card">
                <div class="card card-notice">
                    <div class="card-body">
                        <h4 class="card-title text-info mb-4">Recently Sent Announcements</h4>
                        <div class="notice-scroll-area">
                            <?php 
                            $notices = $conn->query("SELECT * FROM notices ORDER BY id DESC");
                            if($notices->num_rows > 0):
                                while($row = $notices->fetch_assoc()): ?>
                                    <div class="notice-card-item">
                                        <div class="notice-card-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div class="notice-card-body"><?php echo nl2br(htmlspecialchars($row['message'])); ?></div>
                                        <div class="notice-card-footer">
                                            <i class="mdi mdi-clock-outline"></i> 
                                            Posted on: <?php echo date('d M, Y - h:i A', strtotime($row['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endwhile; 
                            else: ?>
                                <div class="text-center py-5">
                                    <i class="mdi mdi-bell-off-outline text-muted" style="font-size: 40px;"></i>
                                    <p class="text-muted mt-2">No notices have been sent yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
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