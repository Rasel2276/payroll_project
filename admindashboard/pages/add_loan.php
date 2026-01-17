<?php
// ১. সেশন শুরু
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/admin_auth.php';

// --- ২. ডাটাবেজ কানেকশন ---
$host = 'localhost';
$db   = 'payroll';
$user = 'root';   
$pass = '';       
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// এমপ্লয়ি লিস্ট (Dropdown এর জন্য)
$employees = $conn->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name ASC");

// সেশন থেকে স্ট্যাটাস চেক
$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);

// --- ৩. লোন ইনসার্ট লজিক ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $loan_title = trim($_POST['loan_title'] ?? '');
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $monthly_installment = floatval($_POST['monthly_installment'] ?? 0);
    $loan_date = $_POST['loan_date'] ?? date('Y-m-d');

    if ($user_id > 0 && $total_amount > 0) {
        // Remaining balance শুরুতে টোটাল অ্যামাউন্টের সমান হবে
        $remaining_balance = $total_amount;

        $stmt = $conn->prepare("INSERT INTO employee_loans (user_id, loan_title, total_amount, monthly_installment, remaining_balance, loan_date, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->bind_param("isddds", $user_id, $loan_title, $total_amount, $monthly_installment, $remaining_balance, $loan_date);

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
        <h3 class="text-white mb-4">Add Employee Loan / Advance</h3>

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
                <div class="col-full">
                    <div class="floating-label">
                        <input type="text" name="loan_title" required placeholder=" ">
                        <label>Loan Title (e.g. Advance Salary, Laptop Loan)</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="total_amount" required placeholder=" ">
                        <label>Total Loan Amount</label>
                    </div>
                </div>
                <div class="col">
                    <div class="floating-label">
                        <input type="number" step="0.01" name="monthly_installment" required placeholder=" ">
                        <label>Monthly Installment</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-full">
                    <div class="floating-label">
                        <input type="date" name="loan_date" value="<?php echo date('Y-m-d'); ?>" required placeholder=" ">
                        <label>Loan Disbursement Date</label>
                    </div>
                </div>
            </div>

            <div class="submit-row" style="margin-top: 20px;">
                <button type="submit">Save Loan Record</button>
            </div>
        </form>

        <style>
            .employee-form { background: #191C24; padding: 25px; border-radius: 10px; max-width: 1200px; }
            .row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
            .col { flex: 1 1 45%; } 
            .col-full { flex: 1 1 100%; }
            .floating-label { position: relative; margin-top: 15px; }
            
            .floating-label input, .floating-label select {
                width: 100%; padding: 12px; border: 1px solid #555; border-radius: 5px; 
                background: transparent !important; color: #fff; box-sizing: border-box; outline: none;
                appearance: none;
            }
            .floating-label select option { background-color: #191C24 !important; color: #fff; }
            
            .floating-label label {
                position: absolute; left: 12px; top: 12px; color: #aaa; 
                background: #191C24; padding: 0 5px; transition: .2s; pointer-events: none;
            }
            
            .floating-label input:focus + label,
            .floating-label input:not(:placeholder-shown) + label,
            .floating-label select:focus + label,
            .floating-label select:valid + label {
                top: -8px; font-size: 12px; color: #aaa;
            }
            .floating-label input:focus, .floating-label select:focus { border-color: #4BB543; }

            button {
                background: #4BB543; color: #fff; border: none; padding: 12px 35px; 
                border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s;
            }
            
        </style>
    </div>

    <script>
    <?php if($status === "success"): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Loan record has been added successfully.',
            icon: 'success',
            confirmButtonColor: '#4BB543',
            background: '#191C24',
            color: '#fff'
        });
    <?php elseif($status === "error"): ?>
        Swal.fire({
            title: 'Error!',
            text: 'Database error. Could not save loan.',
            icon: 'error',
            confirmButtonColor: '#fc424a',
            background: '#191C24',
            color: '#fff'
        });
    <?php elseif($status === "invalid"): ?>
        Swal.fire({
            title: 'Wait!',
            text: 'Please fill all required fields correctly.',
            icon: 'warning',
            confirmButtonColor: '#ffab00',
            background: '#191C24',
            color: '#fff'
        });
    <?php endif; ?>
    </script>

    <?php include '../includes/footer.php'; ?>
</div>