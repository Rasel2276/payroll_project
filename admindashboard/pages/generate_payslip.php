<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/admin_auth.php';

$host = 'localhost'; $db = 'payroll'; $user = 'root'; $pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("DB Connection failed: " . $conn->connect_error); }

// --- ১. AJAX Request Logic ---
if(isset($_GET['get_emp_stats'])){
    $uid = intval($_GET['get_emp_stats']);
    $month_name = $_GET['month'];
    $year = intval($_GET['year']);
    
    $month_num = date('m', strtotime($month_name));
    $total_days = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);

    $working_days_count = 0;
    for ($i = 1; $i <= $total_days; $i++) {
        $day_of_week = date('N', strtotime("$year-$month_num-$i"));
        if ($day_of_week != 5 && $day_of_week != 6) { $working_days_count++; }
    }

    $u_res = $conn->query("SELECT basic_salary FROM users WHERE id = $uid")->fetch_assoc();
    $alw = $conn->query("SELECT * FROM allowances WHERE user_id = $uid")->fetch_assoc();
    $loan = $conn->query("SELECT monthly_installment FROM employee_loans WHERE user_id = $uid AND status = 'Active' LIMIT 1")->fetch_assoc();
    
    $att_res = $conn->query("SELECT SUM(CASE WHEN total_hours > 8 THEN total_hours - 8 ELSE 0 END) as ot_hrs, COUNT(*) as p_days 
                             FROM attendance WHERE user_id = $uid AND MONTH(attendance_date) = $month_num AND YEAR(attendance_date) = $year 
                             AND (status = 'Present' OR status = 'Late')")->fetch_assoc();

    $lv_res = $conn->query("SELECT COUNT(*) as total FROM leave_requests WHERE user_id = $uid AND MONTH(start_date) = $month_num AND status = 'Approved'")->fetch_assoc();

    $present = ($att_res['p_days'] ?? 0) + ($lv_res['total'] ?? 0);
    $absent = max(0, $working_days_count - $present);
    $basic = floatval($u_res['basic_salary'] ?? 0);
    $daily_wage = ($working_days_count > 0) ? ($basic / $working_days_count) : 0;

    echo json_encode([
        'basic' => $basic, 'medical' => $alw['medical_allowance'] ?? 0, 'house' => $alw['house_rent'] ?? 0,
        'transport' => $alw['transport_allowance'] ?? 0, 'other_allow' => $alw['other_allowance'] ?? 0,
        'loan' => $loan['monthly_installment'] ?? 0, 'work_days' => $working_days_count,
        'pres_days' => $present, 'abs_days' => $absent, 'abs_penalty' => round($daily_wage * $absent, 2),
        'ot_hours' => round($att_res['ot_hrs'] ?? 0, 2),
        'ot_amount' => round((($daily_wage/8)*2) * ($att_res['ot_hrs'] ?? 0), 2)
    ]);
    exit;
}

// --- ২. Form Submission Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_POST['user_id'];
    $month = $_POST['month'];
    $year = intval($_POST['year']);
    
    $check = $conn->query("SELECT id FROM payslips WHERE user_id=$uid AND month='$month' AND year=$year");
    if($check->num_rows > 0) {
        $_SESSION['error'] = "Payslip already generated for this month!";
    } else {
        $sql = "INSERT INTO payslips (user_id, month, year, basic_salary, medical_allowance, house_rent, transport_allowance, other_allowance, overtime_amount, bonus, other_earnings, absent_count, absent_deduction, salary_advance_deduction, home_loan_deduction, other_deductions, gross_salary, total_deduction, net_salary, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $types = "isiddddddddiddddddd"; 
        $stmt->bind_param($types, 
            $uid, $month, $year, $_POST['basic_salary'], $_POST['medical_allowance'], 
            $_POST['house_rent'], $_POST['transport_allowance'], $_POST['other_allowance'], 
            $_POST['overtime_amount'], $_POST['bonus'], $_POST['other_earnings'], 
            $_POST['absent_count'], $_POST['absent_deduction'], $_POST['salary_advance_deduction'], 
            $_POST['home_loan_deduction'], $_POST['other_deductions'], $_POST['gross_salary'], 
            $_POST['total_deduction'], $_POST['net_salary']
        );
        
        if($stmt->execute()) { $_SESSION['success'] = "Payslip saved successfully!"; }
        else { $_SESSION['error'] = "Database Error: " . $conn->error; }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$employees = $conn->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name ASC");
?>

<?php include '../includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="main-panel">
    <div class="content-wrapper">
        <h3 class="mb-4">Generate Monthly Payslip</h3>
        
        <form method="POST" id="payslipForm" class="payslip-custom-form">
            <div class="payslip-stats-row">
                <div class="ps-stat-box">Work Days: <b id="disp_work">0</b></div>
                <div class="ps-stat-box">Present: <b id="disp_pres" style="color:#4BB543">0</b></div>
                <div class="ps-stat-box">Absent: <b id="disp_abs" style="color:#ff4d4f">0</b></div>
                <div class="ps-stat-box">OT Hrs: <b id="disp_ot" style="color:#ffab00">0.00</b></div>
                <input type="hidden" name="absent_count" id="absent_count_input">
            </div>

            <div class="payslip-grid-row">
                <div class="ps-col">
                    <div class="ps-floating-label">
                        <select name="user_id" id="user_id" required class="fetch-trigger">
                            <option value="">Select Employee</option>
                            <?php while($emp = $employees->fetch_assoc()): ?>
                                <option value="<?= $emp['id'] ?>"><?= $emp['name'] ?> (ID: <?= $emp['id'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <label>Employee Name</label>
                    </div>
                </div>
                <div class="ps-col">
                    <div class="ps-floating-label">
                        <select name="month" id="month" class="fetch-trigger" required>
                            <?php foreach(["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"] as $m) 
                                echo "<option value='$m' ".($m==date('F')?'selected':'').">$m</option>"; ?>
                        </select>
                        <label>Month</label>
                    </div>
                </div>
                <div class="ps-col">
                    <div class="ps-floating-label">
                        <input type="number" name="year" id="year" class="fetch-trigger" value="<?= date('Y') ?>" placeholder=" " required>
                        <label>Year</label>
                    </div>
                </div>
            </div>

            <h4 class="ps-section-title ps-earn">Earnings (+)</h4>
            <div class="payslip-grid-row">
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="basic_salary" id="basic" class="calc" placeholder=" " required><label>Basic Salary</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="medical_allowance" id="medical" class="calc" placeholder=" "><label>Medical</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="house_rent" id="house" class="calc" placeholder=" "><label>House Rent</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="transport_allowance" id="transport" class="calc" placeholder=" "><label>Transport</label></div></div>
            </div>
            <div class="payslip-grid-row">
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="other_allowance" id="other_allow" class="calc" placeholder=" "><label>Other Allow.</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="overtime_amount" id="ot_amount" class="calc" placeholder=" "><label>OT Amount</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="bonus" class="calc" value="0.00" placeholder=" "><label>Bonus</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="other_earnings" class="calc" value="0.00" placeholder=" "><label>Other Earn.</label></div></div>
            </div>

            <h4 class="ps-section-title ps-deduct">Deductions (-)</h4>
            <div class="payslip-grid-row">
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="absent_deduction" id="abs_ded" class="calc-ded" placeholder=" "><label>Absent Ded.</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="salary_advance_deduction" class="calc-ded" value="0.00" placeholder=" "><label>Advance Ded.</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="home_loan_deduction" id="loan" class="calc-ded" placeholder=" "><label>Loan Inst.</label></div></div>
                <div class="ps-col"><div class="ps-floating-label"><input type="number" step="0.01" name="other_deductions" class="calc-ded" value="0.00" placeholder=" "><label>Other Ded.</label></div></div>
            </div>

            <div class="ps-total-bar">
                <div class="ps-total-item">Gross: <span id="txt_gross">0.00</span><input type="hidden" name="gross_salary" id="gross_val"></div>
                <div class="ps-total-item">Deduction: <span id="txt_deduct" style="color:#ff4d4f">0.00</span><input type="hidden" name="total_deduction" id="deduct_val"></div>
                <div class="ps-total-item">Net Salary: <span id="txt_net" style="color:#4BB543">0.00</span><input type="hidden" name="net_salary" id="net_val"></div>
            </div>

            <div class="ps-submit-row">
                <button type="submit" class="ps-btn-submit">Create & Generate Payslip</button>
            </div>
        </form>

        <style>
        /* Unique classes to avoid conflict with theme searchbar/toggle */
        .payslip-custom-form{background:#191C24;padding:25px;border-radius:10px;width:100%;margin-bottom:20px; box-sizing: border-box;}
        .payslip-grid-row{display:flex;gap:20px;margin-bottom:20px;flex-wrap:wrap; box-sizing: border-box;}
        .ps-col{flex:1 1 22%; min-width: 200px; box-sizing: border-box;}
        .ps-floating-label{position:relative;margin-top:15px}
        
        .ps-floating-label input, .ps-floating-label select{
            width:100%; padding:12px; border:1px solid #555; border-radius:5px; 
            background: #191C24 !important; color:#fff;
            -webkit-appearance: none; appearance: none; box-sizing: border-box;
        }
        .ps-floating-label label{position:absolute;left:12px;top:12px;color:#aaa;background:#191C24;padding:0 5px;transition:.2s;pointer-events:none}
        .ps-floating-label input:focus+label, .ps-floating-label input:not(:placeholder-shown)+label,
        .ps-floating-label select:focus+label, .ps-floating-label select:valid+label { top:-8px; font-size:12px; color:#4BB543; }
        
        .ps-section-title{margin:20px 0 10px;font-weight:bold;padding-bottom:5px;border-bottom:1px solid #333}
        .ps-earn{color:#4BB543}.ps-deduct{color:#ff4d4f}
        
        .payslip-stats-row{display:flex;gap:10px;margin-bottom:20px;background:#000;padding:15px;border-radius:5px}
        .ps-stat-box{flex:1;text-align:center;font-size:14px;color:#888}
        .ps-stat-box b{display:block;font-size:18px;color:#fff}

        .ps-total-bar{display:flex;justify-content:space-between;background:#000;padding:20px;border-radius:5px;margin-top:20px;border:1px solid #444}
        .ps-total-item{text-align:center;flex:1;font-weight:bold;color:#aaa}
        .ps-total-item span{display:block;font-size:22px;color:#fff;margin-top:5px}

        .ps-btn-submit{background:#4BB543;color:#fff;border:none;padding:15px 25px;border-radius:5px;cursor:pointer;width:100%;font-size:16px;font-weight:bold;transition: 0.3s;}
        .ps-btn-submit:hover{background: #3ea037; transform: translateY(-2px);}
        </style>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // SweetAlert handling
    <?php if(isset($_SESSION['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Success!', text: '<?= $_SESSION['success'] ?>', background: '#191c24', color: '#fff', confirmButtonColor: '#4BB543' });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        Swal.fire({ icon: 'error', title: 'Oops...', text: '<?= $_SESSION['error'] ?>', background: '#191c24', color: '#fff', confirmButtonColor: '#ff4d4f' });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    function fetchStats() {
        let uid = $('#user_id').val();
        let month = $('#month').val();
        let year = $('#year').val();
        if(!uid) return;
        $.getJSON(`?get_emp_stats=${uid}&month=${month}&year=${year}`, function(data) {
            $('#basic').val(data.basic).trigger('input');
            $('#medical').val(data.medical).trigger('input');
            $('#house').val(data.house).trigger('input');
            $('#transport').val(data.transport).trigger('input');
            $('#other_allow').val(data.other_allow).trigger('input');
            $('#loan').val(data.loan).trigger('input');
            $('#abs_ded').val(data.abs_penalty).trigger('input');
            $('#ot_amount').val(data.ot_amount).trigger('input');
            $('#disp_work').text(data.work_days);
            $('#disp_pres').text(data.pres_days);
            $('#disp_abs').text(data.abs_days);
            $('#disp_ot').text(data.ot_hours);
            $('#absent_count_input').val(data.abs_days);
            calculateTotals();
        });
    }

    function calculateTotals() {
        let gross = 0;
        $('.calc').each(function(){ gross += parseFloat($(this).val() || 0); });
        let deduct = 0;
        $('.calc-ded').each(function(){ deduct += parseFloat($(this).val() || 0); });
        let net = gross - deduct;
        $('#txt_gross').text(gross.toFixed(2)); $('#gross_val').val(gross.toFixed(2));
        $('#txt_deduct').text(deduct.toFixed(2)); $('#deduct_val').val(deduct.toFixed(2));
        $('#txt_net').text(net.toFixed(2)); $('#net_val').val(net.toFixed(2));
    }

    $('.fetch-trigger').on('change', fetchStats);
    $('.calc, .calc-ded').on('input', calculateTotals);
    </script>

<?php include '../includes/footer.php'; ?>