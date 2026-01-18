<?php
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost';
$db   = 'payroll';
$user = 'root';   
$pass = '';       

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}


if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $conn->query("DELETE FROM attendance WHERE id=$del_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

include '../includes/header.php';


$attendance_list = $conn->query("
    SELECT a.*, u.name as emp_name 
    FROM attendance a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.attendance_date DESC, a.id DESC
");
?>

<div class="main-panel">
    <div class="content-wrapper">
        <h3>Employee Attendance List</h3>

        <div style="overflow-x:auto;">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($attendance_list->num_rows > 0): ?>
                        <?php while ($row = $attendance_list->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID"><?php echo $row['id']; ?></td>
                                <td data-label="Name"><?php echo htmlspecialchars($row['emp_name']); ?></td>
                                <td data-label="Date"><?php echo $row['attendance_date']; ?></td>
                                <td data-label="Check In"><?php echo $row['check_in'] ? $row['check_in'] : '--:--'; ?></td>
                                <td data-label="Check Out"><?php echo $row['check_out'] ? $row['check_out'] : '--:--'; ?></td>
                                <td data-label="Hours"><?php echo $row['total_hours']; ?></td>
                                <td data-label="Status">
                                    <?php 
                                        $status_color = ($row['status'] == 'Present') ? '#4BB543' : (($row['status'] == 'Absent') ? '#fc424a' : '#ffab00');
                                    ?>
                                    <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                                    <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                        <a href="edit_attendance.php?id=<?php echo $row['id']; ?>">Edit</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No attendance records found.</td>
                        </tr>
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
                dropdowns.forEach(d => {
                    if (d.id !== 'dropdown-' + id) d.style.display = 'none';
                });
                let dropdown = document.getElementById('dropdown-' + id);
                dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
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
            }

            .employee-table tr:hover {
                background-color: #2e3340;
            }

            .dropbtn {
                background-color: #4BB543;
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
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
                z-index: 1000;
            }

            .dropdown-content a {
                color: #fff;
                padding: 10px 15px;
                text-decoration: none;
                display: block;
            }

            .dropdown-content a:hover {
                background-color: #4BB543;
            }

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
                }
            }
        </style>
    </div>

<?php include '../includes/footer.php'; ?>