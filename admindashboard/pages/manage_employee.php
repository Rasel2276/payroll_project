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
    $conn->query("DELETE FROM users WHERE id=$del_id AND role='employee'");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';
$employees = $conn->query("SELECT * FROM users WHERE role='employee' ORDER BY id DESC");
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title text-white">Manage Employees</h3>
        </div>

        <div class="card" style="background: #191c24; border: 1px solid #2c2e33;">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Designation</th>
                                <th>Salary</th>
                                <th>Actions</th>
                                <th>Login Info</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($employees->num_rows > 0): ?>
                                <?php while($emp = $employees->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $emp['id']; ?></td>
                                    <td data-label="Name"><?php echo htmlspecialchars($emp['name']); ?></td>
                                    <td data-label="Email"><?php echo htmlspecialchars($emp['email']); ?></td>
                                    <td data-label="Designation"><?php echo htmlspecialchars($emp['designation']); ?></td>
                                    <td data-label="Salary"><?php echo number_format($emp['basic_salary'],2); ?></td>
                                    <td data-label="Actions">
                                        <div style="position: relative; display: inline-block;">
                                            <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $emp['id']; ?>)">
                                                Actions &#9662;
                                            </button>
                                            <div class="dropdown-content" id="dropdown-<?php echo $emp['id']; ?>">
                                                <a href="edit_employee.php?id=<?php echo $emp['id']; ?>">Edit</a>
                                                <a href="?delete=<?php echo $emp['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                                <a href="view_employee.php?id=<?php echo $emp['id']; ?>">View</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Login Info">
                                        <a href="../../slip.php?name=<?php echo urlencode($emp['name']); ?>&email=<?php echo urlencode($emp['email']); ?>&password=<?php echo urlencode($emp['password']); ?>" 
                                           target="_blank" class="slip-btn">View Slip</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="text-align:center;">No employees found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
    
        .employee-table { 
            width: 100%; 
            border-collapse: collapse; 
            background-color: #191C24; 
            color: #fff; 
        }
        .employee-table th, .employee-table td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid #2c2e33; 
        }
        .employee-table th { 
            background-color: #2A2E39; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 13px;
        }
        .employee-table tr:hover { 
            background-color: #2e3340; 
        }

 
        .dropbtn { 
            background-color: #4BB543; 
            color: #fff; 
            padding: 8px 16px; 
            font-size: 13px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .slip-btn { 
            background-color: #ffd700; 
            color: #000; 
            padding: 8px 16px; 
            font-size: 12px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-weight: bold; 
            display: inline-block;
        }
        .slip-btn:hover { background-color: #e6c200; }

        
        .dropdown-content { 
            display: none; 
            position: absolute; 
            background-color: #2A2E39; 
            min-width: 130px; 
            border-radius: 4px; 
            box-shadow: 0 8px 16px rgba(0,0,0,0.4); 
            z-index: 1000;
            right: 0;
        }
        .dropdown-content a { 
            color: #fff; 
            padding: 10px 15px; 
            text-decoration: none; 
            display: block; 
            font-size: 13px;
            border-bottom: 1px solid #333;
        }
        .dropdown-content a:last-child { border-bottom: none; }
        .dropdown-content a:hover { background-color: #4BB543; }

        
        @media(max-width:768px){
            .employee-table thead { display: none; }
            .employee-table, .employee-table tbody, .employee-table tr, .employee-table td { 
                display: block; 
                width: 100%; 
            }
            .employee-table tr { 
                margin-bottom: 20px; 
                border: 1px solid #2c2e33; 
            }
            .employee-table td { 
                text-align: right; 
                padding: 12px 15px; 
                position: relative; 
                border-bottom: 1px solid #2c2e33; 
            }
            .employee-table td::before { 
                content: attr(data-label); 
                position: absolute; 
                left: 15px; 
                width: 45%; 
                font-weight: bold; 
                text-align: left; 
                color: #8f5fe8;
            }
        }
    </style>

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
    </script>

    <?php include '../includes/footer.php'; ?>
</div>