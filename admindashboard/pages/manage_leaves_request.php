<?php
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost';
$db = 'payroll';
$user = 'root';   
$pass = '';       
$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// --- Action Logic (Approve/Reject/Delete) ---
if(isset($_GET['status_update']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status_update']);
    $conn->query("UPDATE leave_requests SET status='$status' WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if(isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM leave_requests WHERE id=$del_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// Employee name shoho leave data fetch kora
$sql = "SELECT lr.*, u.name as emp_name FROM leave_requests lr 
        JOIN users u ON lr.user_id = u.id 
        ORDER BY lr.id DESC";
$leaves = $conn->query($sql);
?>

<div class="main-panel">
<div class="content-wrapper">
    <h2 class="text-white mb-4">Manage Leave Applications</h2>

    <div style="overflow-x:auto;">
    <table class="employee-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if($leaves->num_rows > 0): ?>
            <?php while($row = $leaves->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                <td><?php echo $row['start_date']; ?></td>
                <td><?php echo $row['end_date']; ?></td>
                <td>
                    <?php 
                        $status = $row['status'];
                        $color = "#ffd700"; // Pending (Yellow)
                        if($status == 'Approved') $color = "#4BB543"; // Success (Green)
                        if($status == 'Rejected') $color = "#fc424a"; // Danger (Red)
                    ?>
                    <span style="color: <?php echo $color; ?>; font-weight: bold;">
                        <?php echo $status; ?>
                    </span>
                </td>
                <td>
                    <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                    <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                        <?php if($row['status'] == 'Pending'): ?>
                            <a href="?status_update=Approved&id=<?php echo $row['id']; ?>" onclick="return confirm('Approve this leave?')">Approve</a>
                            <a href="?status_update=Rejected&id=<?php echo $row['id']; ?>" onclick="return confirm('Reject this leave?')">Reject</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No leave applications found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
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
    </script>

    <style>
    .employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff;  }
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
</div>
<?php include '../includes/footer.php'; ?>