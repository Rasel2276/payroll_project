<?php 
session_start();

// --- Database Connection ---
$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: ".$conn->connect_error);

// Employee login check
if (!isset($_SESSION['auth_user'])) {
    header("Location: ../../index.php");
    exit();
}

include '../includes/header.php'; 
?>

<style>
    .content-wrapper { 
        background: #000 !important; 
        height: 100vh;
    }
    .page-title {
        color: #ffffff;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }
    /* Notice Card Styling */
    .notice-card-employee {
        background: #191c24;
        border: 1px solid #2c2e33;
        border-radius: 8px;
    }
    .card-title {
        color: #ffab00 !important;
        font-weight: 600;
    }
    /* Table Styling */
    .table-dark-custom {
        background: transparent;
        color: #fff;
    }
    .table-dark-custom thead th {
        background: #2a3038;
        border-color: #2c2e33;
        color: #ffab00;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
    }
    .table-dark-custom td {
        border-color: #2c2e33;
        vertical-align: middle;
        padding: 15px;
    }
    .clickable-row {
        cursor: pointer;
        transition: background 0.3s;
    }
    .clickable-row:hover {
        background: rgba(255, 171, 0, 0.1) !important;
    }
    .notice-badge {
        background: #00d25b;
        color: #fff;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
    }
    .text-truncate-custom {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar { width: 4px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #444; border-radius: 10px; }
</style>

<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header">
            <h3 class="page-title">Announcements Board</h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card notice-card-employee">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-bell-ring-outline mr-2"></i> Latest Notices
                        </h4>
                        
                        <div class="table-responsive">
                            <table class="table table-dark-custom text-center">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Message Preview</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $notices = $conn->query("SELECT * FROM notices ORDER BY id DESC");
                                    
                                    if($notices && $notices->num_rows > 0):
                                        $count = 1;
                                        while($row = $notices->fetch_assoc()): ?>
                                            <tr class="clickable-row" onclick="viewNotice('<?php echo addslashes(htmlspecialchars($row['title'])); ?>', '<?php echo addslashes(nl2br(htmlspecialchars($row['message']))); ?>', '<?php echo date('d M, Y - h:i A', strtotime($row['created_at'])); ?>')">
                                                <td><?php echo $count++; ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d M, Y', strtotime($row['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td class="text-warning font-weight-bold">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </td>
                                                <td>
                                                    <span class="text-truncate-custom text-muted">
                                                        <?php echo htmlspecialchars($row['message']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-outline-warning btn-sm">View Full</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; 
                                    else: ?>
                                        <tr>
                                            <td colspan="5" class="py-5">
                                                <i class="mdi mdi-email-open-outline text-muted" style="font-size: 40px;"></i>
                                                <p class="text-muted mt-2">No new notices available.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include '../includes/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function viewNotice(title, message, date) {
        Swal.fire({
            title: '<h3 style="color:#ffab00; margin-bottom:0;">' + title + '</h3>',
            html: `
                <div style="text-align: left; padding: 10px; border-top: 1px solid #2c2e33; margin-top: 15px;">
                    <p style="color: #6c7293; font-size: 12px; margin-bottom: 15px;">
                        <i class="mdi mdi-clock-outline"></i> Posted on: ${date}
                    </p>
                    <div style="color: #fff; font-size: 15px; line-height: 1.6; background: #2a3038; padding: 15px; border-radius: 5px;">
                        ${message}
                    </div>
                </div>
            `,
            background: '#191c24',
            confirmButtonColor: '#ffab00',
            confirmButtonText: 'Close',
            width: '600px',
            color: '#fff'
        });
    }
</script>