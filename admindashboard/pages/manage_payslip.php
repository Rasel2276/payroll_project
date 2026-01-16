<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Connection failed: " . $conn->connect_error); }

// --- ১. ডিলিট লজিক ---
if(isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM payslips WHERE id=$del_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// --- ২. স্ট্যাটাস আপডেট লজিক (Paid করা) ---
if(isset($_GET['mark_paid'])) {
    $paid_id = intval($_GET['mark_paid']);
    $conn->query("UPDATE payslips SET payment_status='Paid' WHERE id=$paid_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// --- ৩. পে-স্লিপ ডাটা ফেচ করা ---
$payslips = $conn->query("SELECT p.*, u.name as emp_name 
                          FROM payslips p 
                          JOIN users u ON p.user_id = u.id 
                          ORDER BY p.id DESC");
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="mb-4">Manage Payslips</h2>

        <div class="table-container">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Month/Year</th>
                        <th>Gross Salary</th>
                        <th>Deduction</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($payslips->num_rows > 0): ?>
                    <?php while($ps = $payslips->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo $ps['id']; ?></td>
                        <td data-label="Name"><?php echo htmlspecialchars($ps['emp_name']); ?></td>
                        <td data-label="Month/Year"><?php echo $ps['month'] . " " . $ps['year']; ?></td>
                        <td data-label="Gross"><?php echo number_format($ps['gross_salary'], 2); ?></td>
                        <td data-label="Deduction" style="color:#ff4d4f"><?php echo number_format($ps['total_deduction'], 2); ?></td>
                        <td data-label="Net Salary" style="color:#4BB543; font-weight:bold;"><?php echo number_format($ps['net_salary'], 2); ?></td>
                        <td data-label="Status">
                            <span class="badge <?php echo ($ps['payment_status'] == 'Paid') ? 'badge-paid' : 'badge-pending'; ?>">
                                <?php echo $ps['payment_status']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="ps-dropdown">
                                <button class="ps-dropbtn" onclick="toggleDropdown(event, <?php echo $ps['id']; ?>)">
                                    Actions <i class="mdi mdi-chevron-down"></i>
                                </button>
                                <div class="ps-dropdown-content" id="dropdown-<?php echo $ps['id']; ?>">
                                    <a href="view_payslip.php?id=<?php echo $ps['id']; ?>">
                                        <i class="mdi mdi-printer"></i> Print Payslip
                                    </a>
                                    
                                    <?php if($ps['payment_status'] !== 'Paid'): ?>
                                    <a href="?mark_paid=<?php echo $ps['id']; ?>" onclick="return confirm('Mark as Paid?')">
                                        <i class="mdi mdi-cash-check"></i> Mark as Paid
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="javascript:void(0);" class="del-link" onclick="openDeleteModal(<?php echo $ps['id']; ?>)">
                                        <i class="mdi mdi-delete"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">No payslips found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="deleteModal" class="ps-modal">
        <div class="ps-modal-content">
            <i class="mdi mdi-alert-circle-outline" style="font-size: 50px; color: #ff4d4f;"></i>
            <h3>Are you sure?</h3>
            <p>You won't be able to revert this action!</p>
            <div class="ps-modal-buttons">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <a id="confirmDeleteBtn" href="#" class="btn-confirm">Yes, Delete it!</a>
            </div>
        </div>
    </div>

    <style>
        /* Table & Layout */
        .table-container { background-color: #191C24; padding: 15px; border-radius: 10px; }
        .employee-table { width: 100%; border-collapse: collapse; color: #fff; }
        .employee-table th, .employee-table td { padding: 15px; text-align: left; border-bottom: 1px solid #2c2e33; }
        
        /* Table Header White Color */
        .employee-table th { background-color: #0f1015; color: #ffffff !important; text-transform: uppercase; font-size: 13px; font-weight: bold; }
        
        .employee-table tr:hover { background-color: #22252e; }

        /* Status Badge */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: rgba(75, 181, 67, 0.2); color: #4BB543; }
        .badge-pending { background: rgba(255, 171, 0, 0.2); color: #ffab00; }

        /* Dropdown Styles */
        .ps-dropdown { position: relative; display: inline-block; }
        .ps-dropbtn { background-color: #4BB543; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .ps-dropdown-content { display: none; position: absolute; right: 0; background-color: #2A2E39; min-width: 160px; box-shadow: 0px 8px 16px rgba(0,0,0,0.5); z-index: 1000; border-radius: 5px; overflow: hidden; }
        .ps-dropdown-content a { color: #fff; padding: 12px 16px; text-decoration: none; display: block; font-size: 13px; border-bottom: 1px solid #333; }
        .ps-dropdown-content a:hover { background-color: #383d4a; }
        .ps-dropdown-content a i { margin-right: 8px; color: #4BB543; }
        .del-link i { color: #ff4d4f !important; }

        /* Custom Modal Styles */
        .ps-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); }
        .ps-modal-content { background-color: #191c24; margin: 15% auto; padding: 30px; border-radius: 10px; width: 350px; text-align: center; color: #fff; border: 1px solid #333; }
        .ps-modal-buttons { margin-top: 25px; display: flex; justify-content: center; gap: 10px; }
        .btn-cancel { background: #555; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-confirm { background: #ff4d4f; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; text-decoration: none; }
        
        @media(max-width:768px){
            .employee-table thead { display: none; }
            .employee-table td { display: block; text-align: right; padding-left: 50%; position: relative; }
            .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; font-weight: bold; text-align: left; color: #ffffff; }
        }
    </style>

    <script>
        function toggleDropdown(event, id) {
            event.stopPropagation();
            document.querySelectorAll('.ps-dropdown-content').forEach(d => {
                if(d.id !== 'dropdown-'+id) d.style.display = 'none';
            });
            let dropdown = document.getElementById('dropdown-' + id);
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.ps-dropdown-content').forEach(d => d.style.display = 'none');
        });

        // Modal Logic
        function openDeleteModal(id) {
            document.getElementById('confirmDeleteBtn').href = '?delete=' + id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            let modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

<?php include '../includes/footer.php'; ?>