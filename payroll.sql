
CREATE DATABASE payroll;
USE payroll;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee') NOT NULL DEFAULT 'employee',
    designation VARCHAR(100),           
    basic_salary DECIMAL(10,2) DEFAULT 0.00, 
    present_address TEXT,                
    permanent_address TEXT,              
    bank_name VARCHAR(100),             
    bank_account VARCHAR(50),            
    profile_image VARCHAR(255),          
    must_change_password TINYINT(1) DEFAULT 1, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (name, email, password, role, must_change_password) VALUES
('Admin User', 'admin@company.com', 'admin123', 'admin', 0);
