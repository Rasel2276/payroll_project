<?php 
session_start();

// 1. Dynamic ID Picker
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

include '../includes/header.php';

// 2. Fetch Pending Leaves ONLY
$query = "SELECT leave_requests.*, users.name 
          FROM leave_requests 
          JOIN users ON leave_requests.user_id = users.id 
          WHERE leave_requests.user_id = $user_id AND leave_requests.status = 'Pending'
          ORDER BY leave_requests.id DESC";
$result = $conn->query($query);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h2 class="mb-4 text-white">Pending Leave Requests</h2>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Leave Type"><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td data-label="Start Date"><?php echo date('d M, Y', strtotime($row['start_date'])); ?></td>
                            <td data-label="End Date"><?php echo date('d M, Y', strtotime($row['end_date'])); ?></td>
                            <td data-label="Reason"><?php echo htmlspecialchars(substr($row['reason'], 0, 30)); ?>...</td>
                            <td data-label="Status">
                                <span style="color: #ffab00; font-weight: bold;">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">
                                    Actions &#9662;
                                </button>
                                <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                    <a href="#">View Details</a>
                                    <a href="#" style="color: #fc424a;">Cancel Request</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No pending leave applications found.</td>
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
    /* ✅ 100% SAME AS YOUR ATTENDANCE TABLE CSS */
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