<?php
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost';
$db = 'payroll';
$user = 'root';   
$pass = '';       
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);


if(isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM leave_requests WHERE id=$del_id");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';


$sql = "SELECT lr.*, u.name as emp_name FROM leave_requests lr 
        JOIN users u ON lr.user_id = u.id 
        WHERE lr.status IN ('Approved', 'Rejected') 
        ORDER BY lr.id DESC";
$leaves = $conn->query($sql);
?>


<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="text-white mb-4">Leave Application History</h3>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Final Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($leaves->num_rows > 0): ?>
                    <?php while($row = $leaves->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo $row['id']; ?></td>
                        <td data-label="Employee Name"><?php echo htmlspecialchars($row['emp_name']); ?></td>
                        <td data-label="Leave Type"><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td data-label="Start Date"><?php echo $row['start_date']; ?></td>
                        <td data-label="End Date"><?php echo $row['end_date']; ?></td>
                        <td data-label="Status">
                            <?php if($row['status'] == 'Approved'): ?>
                                <span class="status-approved">
                                    <i class="mdi mdi-check-circle"></i> Approved
                                </span>
                            <?php else: ?>
                                <span class="status-rejected">
                                    <i class="mdi mdi-close-circle"></i> Rejected
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Actions">
                            <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Options &#9662;</button>
                            <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this record from history?')">
                                    <i class="mdi mdi-delete"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding: 40px; color: #6c7293;">No processed leave applications found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
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
    </script>

    <style>
   
    .content-wrapper { 
        background-color: #000 !important; 
        min-height: 100vh; 
    }

 
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
        color: #ffffff;
    }

    .employee-table tr:hover { 
        background-color: #2e3340; 
    }

   
    .dropbtn { 
        background-color: #0090e7; 
        color: #fff; 
        padding: 6px 12px; 
        font-size: 14px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        min-width: 80px; 
    }

   
    .dropdown-content { 
        display: none; 
        position: absolute; 
        background-color: #2A2E39; 
        min-width: 150px; 
        border-radius: 5px; 
        box-shadow: 0 8px 16px rgba(0,0,0,0.3); 
        z-index: 1000; 
        right: 10px; 
    }

    .dropdown-content a { 
        color: #fff; 
        padding: 10px 15px; 
        text-decoration: none; 
        display: block; 
    }

    .dropdown-content a:hover { 
        background-color: #fc424a; 
    }


    .status-approved { 
        color: #00d25b; 
        font-weight: bold; 
    }

    .status-rejected { 
        color: #fc424a; 
        font-weight: bold; 
    }

    
    @media(max-width: 768px) {
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
        }
    }
</style>

    <?php include '../includes/footer.php'; ?>
</div>