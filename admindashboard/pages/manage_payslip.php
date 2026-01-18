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


if(isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM payslips WHERE id=$del_id");
    $_SESSION['status'] = "deleted";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if(isset($_GET['mark_paid'])) {
    $paid_id = intval($_GET['mark_paid']);
    $conn->query("UPDATE payslips SET payment_status='Paid' WHERE id=$paid_id");
    $_SESSION['status'] = "paid";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';


$query = "SELECT p.*, u.name as emp_name 
          FROM payslips p 
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.id DESC";
$payslips = $conn->query($query);

$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="text-white mb-4">Manage Payslips</h2>

        <div style="overflow-x:auto;">
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
                                <td data-label="Employee Name"><?php echo htmlspecialchars($ps['emp_name']); ?></td>
                                <td data-label="Month/Year"><?php echo $ps['month'] . " " . $ps['year']; ?></td>
                                <td data-label="Gross Salary"><?php echo number_format($ps['gross_salary'], 2); ?></td>
                                <td data-label="Deduction" style="color:#ff4d4f">
                                    <?php echo number_format($ps['total_deduction'], 2); ?>
                                </td>
                                <td data-label="Net Salary" style="color:#4BB543; font-weight:bold;">
                                    <?php echo number_format($ps['net_salary'], 2); ?>
                                </td>
                                <td data-label="Status">
                                    <span style="color: <?php echo ($ps['payment_status'] == 'Paid') ? '#4BB543' : '#ffab00'; ?>; font-weight:bold;">
                                        <?php echo $ps['payment_status']; ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $ps['id']; ?>)">Actions &#9662;</button>
                                    <div class="dropdown-content" id="dropdown-<?php echo $ps['id']; ?>">
                                        <a href="view_payslip.php?id=<?php echo $ps['id']; ?>">View / Print</a>
                                        <?php if($ps['payment_status'] !== 'Paid'): ?>
                                            <a href="?mark_paid=<?php echo $ps['id']; ?>" onclick="return confirm('Mark this payslip as Paid?')">Mark as Paid</a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" onclick="openDeleteModal(<?php echo $ps['id']; ?>)" style="color: #ff4d4f;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No payslips found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="deleteModal" class="ps-modal">
        <div class="ps-modal-content">
            <i class="mdi mdi-alert-circle-outline" style="font-size: 60px; color: #ff4d4f; display: block; margin-bottom: 15px;"></i>
            <h3 style="color: #fff; margin-bottom: 10px;">Are you sure?</h3>
            <p style="color: #ccc; margin-bottom: 25px;">You won't be able to revert this action!</p>
            <div style="display: flex; justify-content: center; gap: 15px;">
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
            dropdowns.forEach(d => { 
                if(d.id !== 'dropdown-'+id) d.style.display = 'none'; 
            });
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
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #191C24;
            color: #fff;
        }

        .employee-table th, 
        .employee-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        .employee-table th {
            background-color: #2A2E39;
            font-weight: bold;
            color: #fff !important;
        }

        .employee-table tr:hover {
            background-color: #2e3340;
        }

        .dropbtn {
            background-color: #4BB543;
            color: #fff;
            padding: 7px 15px;
            font-size: 13px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            min-width: 90px;
            transition: 0.3s;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2A2E39;
            min-width: 160px;
            border-radius: 5px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            z-index: 1000;
            right: 20px;
        }

        .dropdown-content a {
            color: #fff;
            padding: 12px 15px;
            text-decoration: none;
            display: block;
            font-size: 13px;
            border-bottom: 1px solid #333;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a:hover {
            background-color: #4BB543;
        }

       
        .ps-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.85);
            justify-content: center;
            align-items: center;
        }

        .ps-modal-content {
            background-color: #191c24;
            padding: 40px;
            border-radius: 10px;
            width: 380px;
            text-align: center;
            border: 1px solid #444;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .btn-cancel {
            background: #555;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-confirm {
            background: #ff4d4f;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }

        /* Mobile Responsive */
        @media(max-width:768px) {
            .employee-table thead {
                display: none;
            }

            .employee-table, 
            .employee-table tbody, 
            .employee-table tr, 
            .employee-table td {
                display: block;
                width: 100%;
            }

            .employee-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: 1px solid #333;
            }

            .employee-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                font-weight: bold;
                text-align: left;
                color: #888;
            }
        }
    </style>

    <?php include '../includes/footer.php'; ?>
</div>