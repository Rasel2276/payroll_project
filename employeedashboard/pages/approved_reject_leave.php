<?php 
session_start();

if (isset($_SESSION['auth_user']['id'])) {
    $user_id = $_SESSION['auth_user']['id'];
} else {
    header("Location: ../../index.php");
    exit();
}

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';       
$conn = new mysqli($host, $user, $pass, $db);

// --- 🔴 DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // user_id দিয়ে চেক করা হয়েছে যেন ইউজার শুধু নিজের হিস্ট্রি ডিলিট করতে পারে
    $conn->query("DELETE FROM leave_requests WHERE id=$del_id AND user_id=$user_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';

// Fetch Approved or Rejected Leaves ONLY
$query = "SELECT leave_requests.*, users.name 
          FROM leave_requests 
          JOIN users ON leave_requests.user_id = users.id 
          WHERE leave_requests.user_id = $user_id AND leave_requests.status != 'Pending'
          ORDER BY leave_requests.id DESC";
$result = $conn->query($query);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="text-white">Leave History</h3>

        <div style="overflow-x:auto;" class="mt-4">
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
                            <td data-label="Reason">
                                <?php echo htmlspecialchars(substr($row['reason'], 0, 30)); ?>...
                            </td>
                            <td data-label="Status">
                                <span style="color: <?php echo ($row['status'] == 'Approved') ? '#00d25b' : '#fc424a'; ?>; font-weight: bold;">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                                <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                    <a href="javascript:void(0)" onclick="viewFullReason('<?php echo addslashes(htmlspecialchars($row['reason'])); ?>')">View Details</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No leave history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ড্রপডাউন ফাংশন - হুবহু আপনার Manage Employee ফাইলের মতো
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

function viewFullReason(reason) {
    Swal.fire({
        title: 'Full Reason',
        text: reason,
        background: '#191C24',
        color: '#fff',
        confirmButtonColor: '#4BB543'
    });
}
</script>

<style>
/* CSS - ১০০% হুবহু আপনার Manage Employee ফাইলের মতো */
.content-wrapper{
    height:100vh;
}
.employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
.employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
.employee-table th { background-color: #2A2E39; font-weight: bold; color: #fff; }
.employee-table tr:hover { background-color: #2e3340; }

.dropbtn { background-color: #4BB543; color: #fff; padding: 6px 12px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; min-width: 80px; }

.dropdown-content { display: none; position: absolute; background-color: #2A2E39; min-width: 150px; border-radius: 5px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); z-index: 1000; right: 20px; }
.dropdown-content a { color: #fff; padding: 10px 15px; text-decoration: none; display: block; font-size: 13px; }
.dropdown-content a:hover { background-color: #4BB543; }

@media(max-width:768px){
    .employee-table thead { display: none; }
    .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { display: block; width: 100%; }
    .employee-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #333; }
    .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; font-weight: bold; text-align: left; }
}
</style>