<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
      <!-- partial:partials/_sidebar.htmlllll -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
          <a class="sidebar-brand brand-logo" href="index.html"><img src="/payroll/admindashboard/assets/images/logo.svg" alt="logo" /></a>
          <a class="sidebar-brand brand-logo-mini" href="index.html"><img src="/payroll/admindashboard/assets/images/logo-mini.svg" alt="logo" /></a>
        </div>
        <ul class="nav">
          <li class="nav-item profile">
            <div class="profile-desc">
              <div class="profile-pic">
                <div class="count-indicator">
                  <img class="img-xs rounded-circle " src="/payroll/admindashboard/assets/images/faces/face15.jpg" alt="">
                  <span class="count bg-success"></span>
                </div>
                <div class="profile-name">
                  <h5 class="mb-0 font-weight-normal">
                  <?php 
                   echo isset($_SESSION['auth_user']['name']) ? htmlspecialchars($_SESSION['auth_user']['name']) : 'Guest';
                   ?>
                   </h5>
                  <span>Gold Member</span>
                </div>
              </div>
              <a href="#" id="profile-dropdown" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></a>
              <div class="dropdown-menu dropdown-menu-right sidebar-dropdown preview-list" aria-labelledby="profile-dropdown">
                <a href="#" class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-dark rounded-circle">
                      <i class="mdi mdi-cog text-primary"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <p class="preview-subject ellipsis mb-1 text-small">Account settings</p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-dark rounded-circle">
                      <i class="mdi mdi-onepassword  text-info"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <p class="preview-subject ellipsis mb-1 text-small">Change Password</p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-dark rounded-circle">
                      <i class="mdi mdi-calendar-today text-success"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <p class="preview-subject ellipsis mb-1 text-small">To-do list</p>
                  </div>
                </a>
              </div>
            </div>
          </li>
          <li class="nav-item nav-category">
            <span class="nav-link">Navigation</span>
          </li>
          <li class="nav-item menu-items">
            <a class="nav-link" href="/payroll/admindashboard/index.php">
              <span class="menu-icon">
                <i class="mdi mdi-speedometer"></i>
              </span>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          
          <li class="nav-item menu-items">
            <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
              <span class="menu-icon">
                <i class="mdi mdi-account"></i>
              </span>
              <span class="menu-title">Employee Management</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="/payroll/admindashboard/pages/add_employees.php">Add Employee</a></li>
                <li class="nav-item"> <a class="nav-link" href="/payroll/admindashboard/pages/manage_employee.php">Manage Emplyoee</a></li>
              </ul>
            </div>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="/payroll/admindashboard/pages/employee_attendance_reports.php">
              <span class="menu-icon">
                <i class="mdi mdi-calendar-check"></i>
              </span>
              <span class="menu-title">Attendence</span>
              <i class="menu-arrow"></i>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" data-bs-toggle="collapse" href="#leaveMenu" role="button"
              aria-expanded="false" aria-controls="leaveMenu">
              <span class="menu-icon">
                <i class="mdi mdi-clipboard-text"></i>
              </span>
              <span class="menu-title">Leave Management</span>
              <i class="menu-arrow"></i>
            </a>

            <div class="collapse" id="leaveMenu">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/manage_leaves_request.php">Leave List</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/approved_reject_leave_list.php">Approve Leave</a>
                </li>
              </ul>
            </div>
          </li>


          <li class="nav-item menu-items">
            <a class="nav-link" data-bs-toggle="collapse" href="#payrollMenu" role="button"
              aria-expanded="false" aria-controls="leaveMenu">
              <span class="menu-icon">
                <i class="mdi mdi-cash-multiple"></i>
              </span>
              <span class="menu-title">Payroll Management</span>
              <i class="menu-arrow"></i>
            </a>

            <div class="collapse" id="payrollMenu">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/allowance_add.php">Add Allowance</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/manage_allowances.php">Manage Allowance</a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/add_loan.php">Add Loan</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/manage_loans.php">Manage Loand</a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/generate_payslip.php">Payroll</a>
                </li>

                 <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/manage_payslip.php">Manage Payroll</a>
                </li>
              </ul>
            </div>
          </li>


          <li class="nav-item menu-items">
            <a class="nav-link" data-bs-toggle="collapse" href="#noticeMenu" role="button"
              aria-expanded="false" aria-controls="leaveMenu">
              <span class="menu-icon">
                <i class="mdi mdi-bell-outline"></i>
              </span>
              <span class="menu-title">Notice</span>
              <i class="menu-arrow"></i>
            </a>

            <div class="collapse" id="noticeMenu">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/send_notice.php">Declaire Notice</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="/payroll/admindashboard/pages/notice_manage.php">Manage Notice</a>
                </li>
              </ul>
            </div>
          </li>


          <li class="nav-item menu-items">
            <a class="nav-link" href="/payroll/admindashboard/pages/manage_calendar.php">
              <span class="menu-icon">
                <i class="mdi mdi-cog"></i>
              </span>
              <span class="menu-title">Manage Calendar</span>
            </a>
          </li>

          <li class="nav-item menu-items">
            <a class="nav-link" href="/payroll/admindashboard/pages/payroll_reports.php">
              <span class="menu-icon">
                <i class="mdi mdi-chart-bar"></i>
              </span>
              <span class="menu-title">Payroll Reports</span>
              <i class="menu-arrow"></i>
            </a>
          </li>


        </ul>
      </nav>
      <!-- partial -->