<?php
// ১. সেশন শুরু
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

// সেশন থেকে স্ট্যাটাস চেক
$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);

// --- ২. Notice Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notice_btn'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO notices (title, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $message);
    
    if ($stmt->execute()) {
        $_SESSION['status'] = "success";
    } else {
        $_SESSION['status'] = "error";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

include '../includes/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="text-white mb-4">Broadcast Announcement</h3>

        <form method="POST" class="employee-form">
            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <input type="text" name="title" placeholder=" " required>
                        <label>Notice Title</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <textarea name="message" placeholder=" " required style="height: 120px;"></textarea>
                        <label>Detailed Message</label>
                    </div>
                </div>
            </div>

            <div class="submit-row" style="margin-top: 20px;">
                <button type="submit" name="send_notice_btn">Send to Employees</button>
                <button type="reset" style="background: #555; margin-left: 10px;">Clear</button>
            </div>
        </form>

        <style>
            /* Allowance File CSS - Exact Followed */
            .employee-form { background: #191C24; padding: 25px; border-radius: 10px; max-width: 1200px; }
            .row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
            .col-full { flex: 1 1 100%; }
            
            .floating-label { position: relative; margin-top: 15px; }
            .floating-label input, .floating-label textarea {
                width: 100%; padding: 12px; border: 1px solid #555; border-radius: 5px; 
                background: transparent !important; color: #fff; box-sizing: border-box; outline: none;
                appearance: none;
            }
            
            .floating-label label {
                position: absolute; left: 12px; top: 12px; color: #aaa; 
                background: #191C24; padding: 0 5px; transition: .2s; pointer-events: none;
            }
            
            /* Animation for Floating Labels */
            .floating-label input:focus + label,
            .floating-label input:not(:placeholder-shown) + label,
            .floating-label textarea:focus + label,
            .floating-label textarea:not(:placeholder-shown) + label {
                top: -8px; font-size: 12px; color: #4BB543;
            }
            
            .floating-label input:focus, .floating-label textarea:focus { border-color: #4BB543; }
            
            button {
                background: #4BB543; color: #fff; border: none; padding: 12px 35px; 
                border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s;
            }
            button:hover { background: #3e9e37; }
        </style>
    </div>

    <script>
    <?php if($status === "success"): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Notice broadcasted successfully to all employees.',
            icon: 'success',
            confirmButtonColor: '#4BB543',
            background: '#191C24',
            color: '#fff'
        });
    <?php elseif($status === "error"): ?>
        Swal.fire({
            title: 'Error!',
            text: 'Something went wrong while broadcasting.',
            icon: 'error',
            confirmButtonColor: '#fc424a',
            background: '#191C24',
            color: '#fff'
        });
    <?php endif; ?>
    </script>

    <?php include '../includes/footer.php'; ?>
</div>