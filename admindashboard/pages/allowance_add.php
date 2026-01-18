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


$employees = $conn->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name ASC");


$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = intval($_POST['user_id'] ?? 0);
    $house_rent = trim($_POST['house_rent'] ?? 0);
    $medical   = trim($_POST['medical_allowance'] ?? 0);
    $transport = trim($_POST['transport_allowance'] ?? 0);
    $other     = trim($_POST['other_allowance'] ?? 0);

    if ($user_id > 0) {
        $check = $conn->query("SELECT id FROM allowances WHERE user_id = $user_id");
        
        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE allowances SET house_rent=?, medical_allowance=?, transport_allowance=?, other_allowance=? WHERE user_id=?");
            $stmt->bind_param("ddddi", $house_rent, $medical, $transport, $other, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO allowances (house_rent, medical_allowance, transport_allowance, other_allowance, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ddddi", $house_rent, $medical, $transport, $other, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['status'] = "success";
        } else {
            $_SESSION['status'] = "error";
        }
    } else {
        $_SESSION['status'] = "invalid";
    }


    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="text-white mb-4">Employee Allowances</h3>

        <form method="POST" class="employee-form">
            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <select name="user_id" required class="custom-select">
                            <option value="" hidden selected></option>
                            <?php while($emp = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['name']) . " (ID: " . $emp['id'] . ")"; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <label>Select Employee</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="house_rent" value="0.00" placeholder=" ">
                        <label>House Rent Allowance</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="medical_allowance" value="0.00" placeholder=" ">
                        <label>Medical Allowance</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="transport_allowance" value="0.00" placeholder=" ">
                        <label>Transport Allowance</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="other_allowance" value="0.00" placeholder=" ">
                        <label>Other Allowance</label>
                    </div>
                </div>
            </div>

            <div class="submit-row" style="margin-top: 20px;">
                <button type="submit">Save Allowances</button>
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
                flex: 1 1 45%;
            }

            .col-full {
                flex: 1 1 100%;
            }

            .floating-label {
                position: relative;
                margin-top: 15px;
            }

            .floating-label input,
            .floating-label select {
                width: 100%;
                padding: 12px;
                border: 1px solid #555;
                border-radius: 5px;
                background: transparent !important;
                color: #fff;
                box-sizing: border-box;
                outline: none;
                appearance: none;
            }

            .floating-label select option {
                background-color: #191C24 !important;
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
                pointer-events: none;
            }

            .floating-label input:focus + label,
            .floating-label input:not(:placeholder-shown) + label,
            .floating-label select:focus + label,
            .floating-label select:valid + label {
                top: -8px;
                font-size: 12px;
                color: #aaa;
            }

            .floating-label input:focus,
            .floating-label select:focus {
                border-color: #4BB543;
            }

            button {
                background: #4BB543;
                color: #fff;
                border: none;
                padding: 12px 35px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: bold;
                font-size: 16px;
                transition: 0.3s;
            }

            button:hover {
                background: #3e9e37;
            }
        </style>
    </div>

    <script>
        <?php if($status === "success"): ?>
            Swal.fire({
                title: 'Success!',
                text: 'Allowance data has been saved successfully.',
                icon: 'success',
                confirmButtonColor: '#4BB543',
                background: '#191C24',
                color: '#fff'
            });
        <?php elseif($status === "error"): ?>
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong.',
                icon: 'error',
                confirmButtonColor: '#fc424a',
                background: '#191C24',
                color: '#fff'
            });
        <?php elseif($status === "invalid"): ?>
            Swal.fire({
                title: 'Wait!',
                text: 'Please select a valid employee.',
                icon: 'warning',
                confirmButtonColor: '#ffab00',
                background: '#191C24',
                color: '#fff'
            });
        <?php endif; ?>
    </script>

    <?php include '../includes/footer.php'; ?>
</div>