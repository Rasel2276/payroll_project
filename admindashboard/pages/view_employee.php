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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Employee ID.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'employee'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Employee not found.");
}

$emp = $result->fetch_assoc();
?>

<?php include '../includes/header.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">

        <!-- PAGE HEADER -->
        <div class="page-header-box">
            <h2 class="mb-4">Employee Profile</h2>
            <a href="manage_employees.php" class="back-btn">Back to List</a>
        </div>

        <!-- PROFILE CARD -->
        <div class="employee-form">

            <!-- TOP PROFILE INFO -->
            <div class="profile-top mb-5">
                <div class="avatar-container">
                    <?php if (!empty($emp['profile_image'])): ?>
                        <img src="../../<?php echo htmlspecialchars($emp['profile_image']); ?>" class="emp-img" alt="Profile">
                    <?php else: ?>
                        <img src="../../assets/images/faces/default-user.png" class="emp-img" alt="Default">
                    <?php endif; ?>
                </div>

                <div class="emp-basic-info">
                    <h3><?php echo htmlspecialchars($emp['name']); ?></h3>
                    <p><?php echo htmlspecialchars($emp['designation']); ?></p>
                    <span class="id-badge">Employee ID: #<?php echo $emp['id']; ?></span>
                </div>
            </div>

            <!-- BASIC DETAILS -->
            <div class="row">
                <div class="col">
                    <div class="display-item">
                        <label>Email</label>
                        <div class="value"><?php echo htmlspecialchars($emp['email']); ?></div>
                    </div>
                </div>

                <div class="col">
                    <div class="display-item">
                        <label>Designation</label>
                        <div class="value"><?php echo htmlspecialchars($emp['designation']); ?></div>
                    </div>
                </div>

                <div class="col">
                    <div class="display-item">
                        <label>Basic Salary</label>
                        <div class="value">৳ <?php echo number_format($emp['basic_salary'], 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- BANK & STATUS -->
            <div class="row">
                <div class="col">
                    <div class="display-item">
                        <label>Bank Name</label>
                        <div class="value"><?php echo htmlspecialchars($emp['bank_name'] ?: 'N/A'); ?></div>
                    </div>
                </div>

                <div class="col">
                    <div class="display-item">
                        <label>Bank Account</label>
                        <div class="value"><?php echo htmlspecialchars($emp['bank_account'] ?: 'N/A'); ?></div>
                    </div>
                </div>

                <div class="col">
                    <div class="display-item">
                        <label>Status</label>
                        <div class="value" style="color:#4BB543">Active</div>
                    </div>
                </div>
            </div>

            <!-- ADDRESSES -->
            <div class="row">
                <div class="col">
                    <div class="display-item">
                        <label>Present Address</label>
                        <div class="value address-text">
                            <?php echo nl2br(htmlspecialchars($emp['present_address'])); ?>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="display-item">
                        <label>Permanent Address</label>
                        <div class="value address-text">
                            <?php echo nl2br(htmlspecialchars($emp['permanent_address'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="submit-row mt-4">
                <a href="edit_employee.php?id=<?php echo $emp['id']; ?>" class="action-btn edit">Edit Profile</a>
                <button onclick="window.print()" class="action-btn print">Download PDF</button>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</div>

<style>
/* Dashboard layout styling */
.employee-form {
    background: #191C24;
    padding: 25px;
    border-radius: 10px;
    max-width: 1200px;
    margin-bottom: 20px;
}

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.col {
    flex: 1 1 30%;
    min-width: 250px;
}

.page-header-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
}

.back-btn {
    background: #555;
    color: #fff;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
}

.profile-top {
    display: flex;
    align-items: center;
    gap: 20px;
    border-bottom: 1px solid #333;
    padding-bottom: 20px;
}

.emp-img {
    width: 120px;
    height: 120px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid #4BB543;
}

.emp-basic-info h3 {
    margin: 0;
    color: #fff;
}

.emp-basic-info p {
    color: #aaa;
    margin: 5px 0;
}

.id-badge {
    background: #4BB543;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.display-item {
    border-bottom: 1px solid #333;
    padding: 10px 0;
}

.display-item label {
    font-size: 12px;
    color: #4BB543;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

.display-item .value {
    color: #fff;
    padding-top: 5px;
    font-size: 15px;
}

.address-text {
    background: #0f1015;
    padding: 10px !important;
    border-radius: 5px;
    margin-top: 5px;
    min-height: 60px;
    border: 1px solid #2c2e33;
}

.action-btn {
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    transition: 0.3s;
}

.edit {
    background: #4BB543;
    color: #fff;
    margin-right: 10px;
}

.edit:hover {
    background: #3e9e38;
}

.print {
    background: #0090e7;
    color: #fff;
}

.print:hover {
    background: #0078c1;
}

@media print {
    .sidebar,
    .navbar,
    .back-btn,
    .submit-row,
    .footer {
        display: none !important;
    }

    .main-panel {
        width: 100% !important;
        margin: 0 !important;
        transform: none !important;
    }

    .content-wrapper {
        padding: 0 !important;
        background: white !important;
    }

    .employee-form {
        background: #fff !important;
        color: #000 !important;
        width: 100% !important;
        border: none !important;
    }

    .display-item label {
        color: #555 !important;
    }

    .display-item .value {
        color: #000 !important;
    }

    .display-item {
        border-color: #ddd !important;
    }

    .address-text {
        background: #f9f9f9 !important;
        border: 1px solid #ddd !important;
        color: #333 !important;
    }

    .emp-basic-info h3 {
        color: #000 !important;
    }
}
</style>
