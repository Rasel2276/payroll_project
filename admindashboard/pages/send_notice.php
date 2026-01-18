<?php

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


$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);


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
                        <input type="text" name="title" id="title" placeholder=" " required>
                        <label for="title">Notice Title</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <textarea name="message" id="message" placeholder=" " required style="height: 150px;"></textarea>
                        <label for="message">Detailed Message</label>
                    </div>
                </div>
            </div>

            <div class="submit-row">
                <button type="submit" name="send_notice_btn" class="btn-submit">Send to Employees</button>
                <button type="reset" class="btn-clear">Clear Form</button>
            </div>
        </form>

        <style>
            .employee-form {
                background: #191C24;
                padding: 30px;
                border-radius: 10px;
                max-width: 1000px;
                box-sizing: border-box;
            }

            .row {
                display: flex;
                gap: 20px;
                margin-bottom: 25px;
                flex-wrap: wrap;
            }

            .col-full {
                flex: 1 1 100%;
            }

            .floating-label {
                position: relative;
                margin-top: 10px;
            }

            .floating-label input, 
            .floating-label textarea {
                width: 100%;
                padding: 15px 12px;
                border: 1px solid #333;
                border-radius: 5px;
                background: transparent !important;
                color: #fff;
                box-sizing: border-box;
                outline: none;
                font-size: 15px;
            }

            .floating-label label {
                position: absolute;
                left: 12px;
                top: 15px;
                color: #888;
                background: #191C24;
                padding: 0 5px;
                transition: 0.3s;
                pointer-events: none;
            }

            
            .floating-label input:focus + label,
            .floating-label input:not(:placeholder-shown) + label,
            .floating-label textarea:focus + label,
            .floating-label textarea:not(:placeholder-shown) + label {
                top: -10px;
                font-size: 12px;
                color: #4BB543;
            }

            .floating-label input:focus, 
            .floating-label textarea:focus {
                border-color: #4BB543;
            }

            .submit-row {
                display: flex;
                gap: 15px;
                margin-top: 10px;
            }

            .btn-submit {
                background: #4BB543;
                color: #fff;
                border: none;
                padding: 12px 30px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                font-size: 16px;
                transition: 0.3s;
            }

            .btn-submit:hover {
                background: #3e9e37;
                transform: translateY(-2px);
            }

            .btn-clear {
                background: #555;
                color: #fff;
                border: none;
                padding: 12px 30px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                transition: 0.3s;
            }

            .btn-clear:hover {
                background: #444;
            }
        </style>
    </div>

    <script>
        
        <?php if($status === "success"): ?>
            Swal.fire({
                title: 'Broadcast Success!',
                text: 'The notice has been sent to all employees.',
                icon: 'success',
                confirmButtonColor: '#4BB543',
                background: '#191C24',
                color: '#fff'
            });
        <?php elseif($status === "error"): ?>
            Swal.fire({
                title: 'Broadcast Failed!',
                text: 'Could not send notice. Please check database connection.',
                icon: 'error',
                confirmButtonColor: '#fc424a',
                background: '#191C24',
                color: '#fff'
            });
        <?php endif; ?>
    </script>

    <?php include '../includes/footer.php'; ?>
</div>