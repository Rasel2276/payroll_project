<?php 
session_start();

// 1. Dynamic ID Picker (From your auth_session)
if (isset($_SESSION['auth_user']['id'])) {
    $user_id = $_SESSION['auth_user']['id'];
} else {
    header("Location: ../../index.php");
    exit();
}

// Database Connection
$host = 'localhost';
$db   = 'payroll';
$user = 'root';   
$pass = '';       
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// 2. Delete Action Logic (Only if needed for self-delete)
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Security check: confirm this log belongs to the logged-in user before deleting
    $conn->query("DELETE FROM attendance WHERE id = $del_id AND user_id = $user_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// 3. Fetch Data (Filtered by Logged-in User ID)
// ✅ Ami ekhane WHERE user_id = $user_id add korechi
$query = "SELECT attendance.*, users.name 
          FROM attendance 
          JOIN users ON attendance.user_id = users.id 
          WHERE attendance.user_id = $user_id 
          ORDER BY attendance.attendance_date DESC";
$logs = $conn->query($query);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="mb-4 text-white">My Attendance Report</h2>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee Name</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs && $logs->num_rows > 0): ?>
                        <?php while ($row = $logs->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Date"><?php echo date('d M, Y', strtotime($row['attendance_date'])); ?></td>
                            <td data-label="Employee Name"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td data-label="Check-In"><?php echo $row['check_in']; ?></td>
                            <td data-label="Check-Out"><?php echo $row['check_out']; ?></td>
                            <td data-label="Total Hours"><?php echo $row['total_hours']; ?> hrs</td>
                            <td data-label="Status">
                                <span style="color: <?php echo ($row['status'] == 'Present') ? '#00d25b' : '#fc424a'; ?>;">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">
                                    Actions &#9662;
                                </button>
                                <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                    <a href="#">View Details</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No records found for your account.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>

<script>
    document.addEventListener('click', function() {
        let dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(d => d.style.display = 'none');
    });

    function toggleDropdown(event, id) {
        event.stopPropagation();
        let dropdowns = document.querySelectorAll('.dropdown-content');
        dropdowns.forEach(d => { 
            if(d.id !== 'dropdown-' + id) d.style.display = 'none'; 
        });
        let dropdown = document.getElementById('dropdown-' + id);
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }
</script>

<style>
    /* ... Apnar deya CSS shob ekhane thakbe ... */
    .content-wrapper { background: #000; padding: 2.125rem 2.5rem; min-height: 100vh; }
    .employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
    .employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
    .employee-table th { background-color: #2A2E39; font-weight: bold; text-transform: uppercase; font-size: 13px; }
    .employee-table tr:hover { background-color: #2e3340; }
    .dropbtn { background-color: #4BB543; color: #fff; padding: 7px 15px; font-size: 13px; border: none; border-radius: 4px; cursor: pointer; min-width: 90px; transition: 0.3s; }
    .dropbtn:hover { background-color: #3e9e37; }
    .dropdown-content { display: none; position: absolute; background-color: #2A2E39; min-width: 140px; border-radius: 4px; box-shadow: 0 8px 16px rgba(0,0,0,0.4); z-index: 1000; margin-top: 5px; }
    .dropdown-content a { color: #fff; padding: 10px 15px; text-decoration: none; display: block; font-size: 13px; border-bottom: 1px solid #333; }
    .dropdown-content a:hover { background-color: #4BB543; color: #fff; }
    
    @media(max-width:768px) {
        .employee-table thead { display: none; }
        .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { display: block; width: 100%; }
        .employee-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #333; }
        .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; font-weight: bold; text-align: left; }
    }
</style>