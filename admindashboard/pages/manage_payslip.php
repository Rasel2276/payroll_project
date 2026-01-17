<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// --- ডিলিট লজিক ---
if(isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM payslips WHERE id=$del_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// --- স্ট্যাটাস পেইড লজিক ---
if(isset($_GET['mark_paid'])) {
    $paid_id = intval($_GET['mark_paid']);
    $conn->query("UPDATE payslips SET payment_status='Paid' WHERE id=$paid_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';
$payslips = $conn->query("SELECT p.*, u.name as emp_name FROM payslips p JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");
?>

<div class="main-panel">
<div class="content-wrapper">
<h2>Manage Payslips</h2>

<div style="overflow-x:auto;">
<table class="employee-table">
    <thead>
        <tr>
            <th style="color: #fff !important;">ID</th>
            <th style="color: #fff !important;">Employee Name</th>
            <th style="color: #fff !important;">Month/Year</th>
            <th style="color: #fff !important;">Gross Salary</th>
            <th style="color: #fff !important;">Deduction</th>
            <th style="color: #fff !important;">Net Salary</th>
            <th style="color: #fff !important;">Status</th>
            <th style="color: #fff !important;">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if($payslips->num_rows > 0): ?>
        <?php while($ps = $payslips->fetch_assoc()): ?>
        <tr>
            <td><?php echo $ps['id']; ?></td>
            <td><?php echo htmlspecialchars($ps['emp_name']); ?></td>
            <td><?php echo $ps['month'] . " " . $ps['year']; ?></td>
            <td><?php echo number_format($ps['gross_salary'], 2); ?></td>
            <td style="color:#ff4d4f"><?php echo number_format($ps['total_deduction'], 2); ?></td>
            <td style="color:#4BB543; font-weight:bold;"><?php echo number_format($ps['net_salary'], 2); ?></td>
            <td>
                <span style="color: <?php echo ($ps['payment_status'] == 'Paid') ? '#4BB543' : '#ffab00'; ?>; font-weight:bold;">
                    <?php echo $ps['payment_status']; ?>
                </span>
            </td>
            <td>
                <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $ps['id']; ?>)">Actions &#9662;</button>
                <div class="dropdown-content" id="dropdown-<?php echo $ps['id']; ?>">
                    <a href="view_payslip.php?id=<?php echo $ps['id']; ?>">View / Print</a>
                    <?php if($ps['payment_status'] !== 'Paid'): ?>
                        <a href="?mark_paid=<?php echo $ps['id']; ?>" onclick="return confirm('Mark as Paid?')">Mark as Paid</a>
                    <?php endif; ?>
                    <a href="javascript:void(0);" onclick="openDeleteModal(<?php echo $ps['id']; ?>)" style="color: #ff4d4f;">Delete</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8">No payslips found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>

<div id="deleteModal" class="ps-modal">
    <div class="ps-modal-content">
        <i class="mdi mdi-alert-circle-outline" style="font-size: 50px; color: #ff4d4f; display: block; margin-bottom: 10px;"></i>
        <h3 style="color: #fff; margin-bottom: 10px;">Are you sure?</h3>
        <p style="color: #ccc; margin-bottom: 20px;">You won't be able to revert this action!</p>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="btn-confirm">Yes, Delete it!</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    let dropdowns = document.querySelectorAll('.dropdown-content');
    dropdowns.forEach(d => d.style.display = 'none');
});

function toggleDropdown(event, id) {
    event.stopPropagation();
    let dropdowns = document.querySelectorAll('.dropdown-content');
    dropdowns.forEach(d => { if(d.id !== 'dropdown-'+id) d.style.display = 'none'; });
    let dropdown = document.getElementById('dropdown-' + id);
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

function openDeleteModal(id) {
    document.getElementById('confirmDeleteBtn').href = '?delete=' + id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('deleteModal');
    if (event.target == modal) modal.style.display = "none";
}
</script>

<style>
/* আপনার দেওয়া অরিজিনাল এমপ্লয়ি টেবিল ডিজাইন */
.employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
.employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
.employee-table th { background-color: #2A2E39; font-weight: bold; color: #fff !important; }
.employee-table tr:hover { background-color: #2e3340; }

.dropbtn { background-color: #4BB543; color: #fff; padding: 6px 12px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; min-width: 80px; }
.dropdown-content { display: none; position: absolute; background-color: #2A2E39; min-width: 150px; border-radius: 5px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); z-index: 1000; right: 20px; }
.dropdown-content a { color: #fff; padding: 10px 15px; text-decoration: none; display: block; font-size: 13px; }
.dropdown-content a:hover { background-color: #4BB543; }

/* Modal Styles */
.ps-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
.ps-modal-content { background-color: #191c24; padding: 30px; border-radius: 10px; width: 350px; text-align: center; border: 1px solid #333; }
.btn-cancel { background: #555; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
.btn-confirm { background: #ff4d4f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }

@media(max-width:768px){
    .employee-table thead { display: none; }
    .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { display: block; width: 100%; }
    .employee-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #333; }
    .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; font-weight: bold; text-align: left; }
}
</style>
</div>
<?php include '../includes/footer.php'; ?>