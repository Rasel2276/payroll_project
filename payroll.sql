-- Create database
CREATE DATABASE payroll;
USE payroll;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee') NOT NULL DEFAULT 'employee',
    must_change_password TINYINT(1) DEFAULT 1,  -- ⭐ NEW COLUMN ADDED
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin ----
INSERT INTO users (name, email, password, role, must_change_password) VALUES
('Admin User', 'admin@company.com', 'admin123', 'admin', 0);
