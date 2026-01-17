<?php 
session_start();
require_once __DIR__ . '/../../includes/admin_auth.php'; 

// --- Database Connection ---
$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// --- 🔴 DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM notices WHERE id = $id");
    $_SESSION['status'] = "success";
    $_SESSION['msg'] = "Notice deleted successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

include '../includes/header.php'; 
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="text-white">Manage All Notices</h3>

        <div style="overflow-x:auto;" class="mt-4">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Notice Title</th>
                        <th>Message Snippet</th>
                        <th>Date Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $notices = $conn->query("SELECT * FROM notices ORDER BY id DESC");
                if($notices->num_rows > 0): 
                    while($row = $notices->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo $row['id']; ?></td>
                        <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td data-label="Message">
                            <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($row['message']); ?>
                            </div>
                        </td>
                        <td data-label="Date"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                        <td data-label="Actions">
                            <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                            <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                <a href="javascript:void(0)" onclick="showNotice('<?php echo addslashes(htmlspecialchars($row['title'])); ?>', '<?php echo addslashes(nl2br(htmlspecialchars($row['message']))); ?>')">View</a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this notice?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No notices found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ড্রপডাউন কন্ট্রোল - হুবহু আপনার এমপ্লয়ি ফাইলের মতো
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

// নোটিশ ডিটেইলস দেখানোর ফাংশন (SweetAlert Modal)
function showNotice(title, message) {
    Swal.fire({
        title: '<span style="color: #ffab00;">' + title + '</span>',
        html: '<div style="text-align: left; color: #fff; line-height: 1.6;">' + message + '</div>',
        background: '#191c24',
        confirmButtonColor: '#0090e7',
        confirmButtonText: 'Close'
    });
}
</script>

<style>
/* Table Styling - ১০০% আপনার এমপ্লয়ি ফাইলের মতো */
.employee-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background-color: #191C24; color: #fff; }
.employee-table th, .employee-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #333; }
.employee-table th { background-color: #2A2E39; font-weight: bold; color: #fff; }
.employee-table tr:hover { background-color: #2e3340; }

/* Action Button Styles - হুবহু এক */
.dropbtn { background-color: #4BB543; color: #fff; padding: 6px 12px; font-size: 14px; border: none; border-radius: 5px; cursor: pointer; min-width: 80px; }
.dropdown-content { display: none; position: absolute; background-color: #2A2E39; min-width: 150px; border-radius: 5px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); z-index: 1000; right: 10px; }
.dropdown-content a { color: #fff; padding: 10px 15px; text-decoration: none; display: block; font-size: 13px; cursor: pointer; }
.dropdown-content a:hover { background-color: #4BB543; }

@media(max-width:768px){
    .employee-table thead { display: none; }
    .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { display: block; width: 100%; }
    .employee-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #333; }
    .employee-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; font-weight: bold; text-align: left; color: #4BB543; }
}
</style>

<?php if(isset($_SESSION['status'])): ?>
<script>
    Swal.fire({
        title: 'Success!',
        text: '<?php echo $_SESSION['msg']; ?>',
        icon: 'success',
        background: '#191c24', color: '#fff', confirmButtonColor: '#00d25b'
    });
</script>
<?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>