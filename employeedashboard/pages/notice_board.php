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



<div class="main-panel">
    <div class="content-wrapper">
        
        <div class="page-header">
            <h3 class="page-title">
                <i class="mdi mdi-information-outline"></i> Announcements Board
            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card notice-card-employee">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-bell-ring-outline mr-2"></i> Employee Notifications
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
                                                <td class="col-sl"><?php echo $count++; ?></td>
                                                <td class="col-date">
                                                    <i class="mdi mdi-calendar-text small"></i>
                                                    <?php echo date('d M, Y', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td class="col-subject font-weight-bold">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </td>
                                                <td class="col-preview">
                                                    <span class="text-truncate-custom">
                                                        <?php echo htmlspecialchars($row['message']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-view btn-sm">Read More</button>
                                                </td>
                                            </tr>
                                        <?php endwhile; 
                                    else: ?>
                                        <tr>
                                            <td colspan="5" class="py-5">
                                                <i class="mdi mdi-email-open-outline text-muted" style="font-size: 40px;"></i>
                                                <p class="text-muted mt-2">No new notices available at this moment.</p>
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
            title: '<h3 style="color:#00d25b; margin-bottom:0;">' + title + '</h3>',
            html: `
                <div style="text-align: left; padding: 10px; border-top: 1px solid #2c2e33; margin-top: 15px;">
                    <p style="color: #0090e7; font-size: 13px; margin-bottom: 15px; font-weight: bold;">
                        <i class="mdi mdi-clock-outline"></i> Posted on: ${date}
                    </p>
                    <div style="color: #e4e4e4; font-size: 15px; line-height: 1.6; background: #0d0d0d; padding: 20px; border-radius: 8px; border: 1px solid #2c2e33;">
                        ${message}
                    </div>
                </div>
            `,
            background: '#191c24',
            confirmButtonColor: '#8f5fe8',
            confirmButtonText: 'Got it!',
            width: '600px',
            color: '#fff'
        });
    }
</script>

<style>
    .content-wrapper { 
        background: #000 !important; 
        min-height: 100vh;
    }
    .page-title {
        color: #f7f7f7;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
   
    .notice-card-employee {
        background: #191c24;
        border: 1px solid #2c2e33;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }
    .card-title {
        color: #f7f7f7 !important;
        font-weight: 600;
    }
    
    .table-dark-custom {
        background: transparent;
        color: #e4e4e4;
    }
    .table-dark-custom thead th {
        background: #0d0d0d;
        border-color: #2c2e33;
        color: #8f5fe8;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: bold;
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
        background: rgba(143, 95, 232, 0.1) !important;
    }

    .col-sl { color: #6c7293; }
    .col-date { color: #0090e7; }
    .col-subject { color: #ffab00; font-size: 1rem; } 
    .col-preview { color: #a1a1a1; }

    .text-truncate-custom {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
    }
    
    .btn-view {
        background-color: transparent;
        border: 1px solid #8f5fe8;
        color: #8f5fe8;
        transition: 0.3s;
    }
    .btn-view:hover {
        background-color: #8f5fe8;
        color: #fff;
    }

    .table-responsive::-webkit-scrollbar { width: 4px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #8f5fe8; border-radius: 10px; }
</style>