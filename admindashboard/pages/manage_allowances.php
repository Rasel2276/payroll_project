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

// --- ৩. ডিলিট লজিক (যদি ডিলিট করতে চান) ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM allowances WHERE id=$del_id");
    $_SESSION['status'] = "deleted";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// এলাউন্স টেবিলের ডাটা এবং সাথে ইউজারের নাম জয়েন করে নিয়ে আসা
$query = "SELECT a.*, u.name as emp_name 
          FROM allowances a 
          JOIN users u ON a.user_id = u.id 
          ORDER BY a.id DESC";
$allowances = $conn->query($query);

$status = $_SESSION['status'] ?? "";
unset($_SESSION['status']);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="text-white mb-4">Manage Employee Allowances</h2>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>House Rent</th>
                        <th>Medical</th>
                        <th>Transport</th>
                        <th>Other</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($allowances->num_rows > 0): ?>
                    <?php while ($row = $allowances->fetch_assoc()): 
                        $total = $row['house_rent'] + $row['medical_allowance'] + $row['transport_allowance'] + $row['other_allowance'];
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td><?php echo number_format($row['house_rent'], 2); ?></td>
                        <td><?php echo number_format($row['medical_allowance'], 2); ?></td>
                        <td><?php echo number_format($row['transport_allowance'], 2); ?></td>
                        <td><?php echo number_format($row['other_allowance'], 2); ?></td>
                        <td style="color: #4BB543; font-weight: bold;"><?php echo number_format($total, 2); ?></td>
                        <td>
                            <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                            <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                <a href="edit_allowance.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirmDelete(event, this.href)">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align: center;">No allowance records found.</td></tr>
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

    // ডিলিট কনফার্মেশন মডাল
    function confirmDelete(e, url) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This record will be permanently deleted!",
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

    // স্ট্যাটাস মেসেজ দেখানো
    <?php if($status === "deleted"): ?>
        Swal.fire({
            title: 'Deleted!',
            text: 'Allowance record has been removed.',
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