<?php
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost';
$db   = 'payroll';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name              = trim($_POST['name'] ?? '');
    $email             = trim($_POST['email'] ?? '');
    $contact_no        = trim($_POST['contact_no'] ?? ''); 
    $designation       = trim($_POST['designation'] ?? '');
    $basic_salary      = trim($_POST['basic_salary'] ?? 0);
    $bank_name         = trim($_POST['bank_name'] ?? '');
    $bank_account      = trim($_POST['bank_account'] ?? '');
    $present_address   = trim($_POST['present_address'] ?? '');
    $permanent_address = trim($_POST['permanent_address'] ?? '');

    if (!$name || !$email) {
        $error = "Provide name and email.";
    } else {
        
        $password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);

        
        $profile_image = null;
        if (!empty($_FILES['profile_image']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $profile_image = 'uploads/' . uniqid('emp_') . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . basename($profile_image));
        }

        $stmt = $conn->prepare("
            INSERT INTO users 
            (name, email, contact_no, password, role, designation, basic_salary, bank_name, bank_account, present_address, permanent_address, profile_image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $role = 'employee';
        $stmt->bind_param("sssssssdssss", $name, $email, $contact_no, $password, $role, $designation, $basic_salary, $bank_name, $bank_account, $present_address, $permanent_address, $profile_image);

        if ($stmt->execute()) {
            $success = "Employee added successfully. You can view the login slip in the Manage Employees page.";
        } else {
            $error = "Error adding employee: " . $stmt->error;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <h3>Add Employee</h3>

        <?php if(isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="employee-form">
            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="text" name="name" required placeholder=" ">
                        <label>Name</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="email" name="email" required placeholder=" ">
                        <label>Email</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="text" name="contact_no" placeholder=" ">
                        <label>Contact Number</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="text" name="designation" placeholder=" ">
                        <label>Designation</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="basic_salary" placeholder=" ">
                        <label>Basic Salary</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="text" name="bank_name" placeholder=" ">
                        <label>Bank Name</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="text" name="bank_account" placeholder=" ">
                        <label>Bank Account</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <textarea name="present_address" placeholder=" "></textarea>
                        <label>Present Address</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <textarea name="permanent_address" placeholder=" "></textarea>
                        <label>Permanent Address</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <input type="file" name="profile_image" accept="image/*">
                        <label>Profile Image</label>
                    </div>
                </div>
            </div>

            <div class="submit-row">
                <button type="submit">Create Employee</button>
            </div>
        </form>

        <style>
            .employee-form {
                background: #191C24;
                padding: 25px;
                border-radius: 10px;
                max-width: 1200px;
            }

            .row {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .col {
                flex: 1 1 30%;
            }

            .col-full {
                flex: 1 1 100%;
            }

            .floating-label {
                position: relative;
                margin-top: 15px;
            }

            .floating-label input,
            .floating-label textarea {
                width: 100%;
                padding: 12px;
                border: 1px solid #555;
                border-radius: 5px;
                background: transparent;
                color: #fff;
            }

            .floating-label label {
                position: absolute;
                left: 12px;
                top: 12px;
                color: #aaa;
                background: #191C24;
                padding: 0 5px;
                transition: .2s;
            }

            .floating-label input:focus + label,
            .floating-label input:not(:placeholder-shown) + label,
            .floating-label textarea:focus + label,
            .floating-label textarea:not(:placeholder-shown) + label {
                top: -8px;
                font-size: 12px;
                color: #4BB543;
            }

            button {
                background: #4BB543;
                color: #fff;
                border: none;
                padding: 12px 25px;
                border-radius: 5px;
                cursor: pointer;
            }

            .error {
                color: #ff4d4f;
                font-weight: bold;
            }

            .success {
                color: #4BB543;
                font-weight: bold;
            }
        </style>
    </div>

<?php include '../includes/footer.php'; ?>