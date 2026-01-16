<?php
// ১. সেশন এবং অথেনটিকেশন চেক
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

// --- ৩. ডিলিট লজিক ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM employee_loans WHERE id=$del_id");
    $_SESSION['status'] = "deleted";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// লোন টেবিলের ডাটা এবং ইউজারের নাম জয়েন করে নিয়ে আসা
$query = "SELECT l.*, u.name as emp_name 
          FROM employee_loans l 
          JOIN users u ON l.user_id = u.id 
          ORDER BY l.id DESC";
$loans = $conn->query($query);

$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="text-white mb-4">Manage Employee Loans</h2>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Loan Title</th>
                        <th>Total Amount</th>
                        <th>Installment</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($loans->num_rows > 0): ?>
                    <?php while ($row = $loans->fetch_assoc()): 
                        // স্ট্যাটাস অনুযায়ী কালার নির্ধারণ
                        $status_color = "#4BB543"; // Active
                        if($row['status'] == 'Completed') $status_color = "#ffd700";
                        if($row['status'] == 'Paused') $status_color = "#fc424a";
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['loan_title']); ?></td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo number_format($row['monthly_installment'], 2); ?></td>
                        <td style="color: #fc424a; font-weight: bold;"><?php echo number_format($row['remaining_balance'], 2); ?></td>
                        <td>
                            <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                            <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                <a href="edit_loan.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="confirmDelete(event, this.href)">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align: center;">No loan records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // ড্রপডাউন কন্ট্রোল
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

    // ডিলিট কনফার্মেশন
    function confirmDelete(e, url) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This loan record will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#fc424a',
            cancelButtonColor: '#555',
            confirmButtonText: 'Yes, delete it!',
            background: '#191C24',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }

    // ডিলিট সাকসেস মেসেজ
    <?php if($status === "deleted"): ?>
        Swal.fire({
            title: 'Deleted!',
            text: 'Loan record has been removed.',
            icon: 'success',
            background: '#191C24',
            color: '#fff',
            confirmButtonColor: '#4BB543'
        });
    <?php endif; ?>
    </script>

    <style>
        .employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
        .employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
        .employee-table th { background-color: #2A2E39; font-weight: bold; }
        .employee-table tr:hover { background-color: #2e3340; }

        .dropbtn { background-color: #4BB543; color: #fff; padding: 6px 12px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; min-width: 80px; }
        
        .dropdown-content { display: none; position: absolute; background-color: #2A2E39; min-width: 150px; border-radius: 5px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); z-index: 1000; }
        .dropdown-content a { color: #fff; padding: 10px 15px; text-decoration: none; display: block; }
        .dropdown-content a:hover { background-color: #4BB543; }

        @media(max-width:768px){
            .employee-table thead { display: none; }
            .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { display: block; width: 100%; }
            .employee-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #333; }
            .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; font-weight: bold; text-align: left; }
        }
    </style>

    <?php include '../includes/footer.php'; ?>
</div>