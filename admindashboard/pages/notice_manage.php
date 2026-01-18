<?php 
session_start();
require_once __DIR__ . '/../../includes/admin_auth.php'; 


$host = 'localhost'; 
$db = 'payroll'; 
$user = 'root'; 
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}


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
        <h3 class="text-white mb-4">Manage All Notices</h3>

        <div style="overflow-x:auto;">
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
                            <td data-label="Title">
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></span>
                            </td>
                            <td data-label="Message">
                                <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #aaa;">
                                    <?php echo htmlspecialchars($row['message']); ?>
                                </div>
                            </td>
                            <td data-label="Date"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                            <td data-label="Actions">
                                <button class="dropbtn" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">Actions &#9662;</button>
                                <div class="dropdown-content" id="dropdown-<?php echo $row['id']; ?>">
                                    <a href="javascript:void(0)" onclick="showNotice('<?php echo addslashes(htmlspecialchars($row['title'])); ?>', '<?php echo addslashes(nl2br(htmlspecialchars($row['message']))); ?>')">
                                        <i class="mdi mdi-eye text-primary"></i> View Notice
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" style="color: #ff4d4f;">
                                        <i class="mdi mdi-delete text-danger"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No notices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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
            color: #fff !important;
        }

        .employee-table tr:hover {
            background-color: #2e3340;
        }

        .dropbtn {
            background-color: #4BB543;
            color: #fff;
            padding: 7px 15px;
            font-size: 13px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            min-width: 90px;
            transition: 0.3s;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #2A2E39;
            min-width: 160px;
            border-radius: 5px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            z-index: 1000;
            right: 20px;
        }

        .dropdown-content a {
            color: #fff;
            padding: 12px 15px;
            text-decoration: none;
            display: block;
            font-size: 13px;
            border-bottom: 1px solid #333;
        }

        .dropdown-content a:hover {
            background-color: #4BB543;
        }

        @media(max-width:768px) {
            .employee-table thead {
                display: none;
            }

            .employee-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #333;
            }

            .employee-table td {
                display: block;
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
                color: #4BB543;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

     
        function showNotice(title, message) {
            Swal.fire({
                title: '<span style="color: #ffab00;">' + title + '</span>',
                html: '<div style="text-align: left; color: #fff; line-height: 1.6; padding: 10px;">' + message + '</div>',
                background: '#191c24',
                confirmButtonColor: '#4BB543',
                confirmButtonText: 'Got it'
            });
        }

      
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This notice will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4f',
                cancelButtonColor: '#555',
                confirmButtonText: 'Yes, delete it!',
                background: '#191c24',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_id=' + id;
                }
            })
        }
    </script>

    <?php if(isset($_SESSION['status'])): ?>
    <script>
        Swal.fire({
            title: 'Success!',
            text: '<?php echo $_SESSION['msg']; ?>',
            icon: 'success',
            background: '#191c24', 
            color: '#fff', 
            confirmButtonColor: '#4BB543'
        });
    </script>
    <?php unset($_SESSION['status']); unset($_SESSION['msg']); endif; ?>

    <?php include '../includes/footer.php'; ?>
</div>