
CREATE DATABASE payroll;
USE payroll;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact_no VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    designation VARCHAR(100),           
    basic_salary DECIMAL(10, 2) DEFAULT 0.00, 
    present_address TEXT,                
    permanent_address TEXT,              
    bank_name VARCHAR(100),             
    bank_account VARCHAR(50),            
    profile_image VARCHAR(255),          
    must_change_password TINYINT(1) DEFAULT 1, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in TIME DEFAULT NULL,
    check_out TIME DEFAULT NULL,
    total_hours DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('Present', 'Absent', 'Late', 'Leave') DEFAULT 'Present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type ENUM('Casual', 'Sick') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_remark TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `holiday_date` DATE NOT NULL,
  `holiday_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `holiday_date` (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE allowances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    house_rent DECIMAL(10,2) DEFAULT 0.00,
    medical_allowance DECIMAL(10,2) DEFAULT 0.00,
    transport_allowance DECIMAL(10,2) DEFAULT 0.00,
    other_allowance DECIMAL(10,2) DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE employee_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    loan_title VARCHAR(255) NOT NULL,           
    total_amount DECIMAL(10,2) NOT NULL,        
    monthly_installment DECIMAL(10,2) NOT NULL,
    remaining_balance DECIMAL(10,2) NOT NULL,   
    loan_date DATE NOT NULL,                    
    status ENUM('Active', 'Completed', 'Paused') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE payslips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    
    
    basic_salary DECIMAL(10,2) NOT NULL,
    medical_allowance DECIMAL(10,2) DEFAULT 0.00,
    house_rent DECIMAL(10,2) DEFAULT 0.00,
    transport_allowance DECIMAL(10,2) DEFAULT 0.00,
    other_allowance DECIMAL(10,2) DEFAULT 0.00,
    overtime_amount DECIMAL(10,2) DEFAULT 0.00,
    bonus DECIMAL(10,2) DEFAULT 0.00,
    other_earnings DECIMAL(10,2) DEFAULT 0.00,
    
   
    absent_count INT DEFAULT 0,
    absent_deduction DECIMAL(10,2) DEFAULT 0.00,
    salary_advance_deduction DECIMAL(10,2) DEFAULT 0.00,
    home_loan_deduction DECIMAL(10,2) DEFAULT 0.00,
    other_deductions DECIMAL(10,2) DEFAULT 0.00,
    

    gross_salary DECIMAL(10,2) NOT NULL,
    total_deduction DECIMAL(10,2) NOT NULL,
    net_salary DECIMAL(10,2) NOT NULL,
    
    payment_status ENUM('Paid', 'Unpaid', 'Pending') DEFAULT 'Pending',
    payment_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payslip_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role, must_change_password) VALUES
('Admin User', 'admin@company.com', 'admin123', 'admin', 0);
