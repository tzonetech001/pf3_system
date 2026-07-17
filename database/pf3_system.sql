-- Integrated Digital PF3 Coordination and Medical Reporting System Database

CREATE DATABASE IF NOT EXISTS pf3_system;
USE pf3_system;

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors table
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Police Officers table
CREATE TABLE police_officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    rank VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pf3_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    age INT NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    guardian_phone VARCHAR(20),
    incident_date_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PF3 Cases table
CREATE TABLE pf3_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pf3_number VARCHAR(20) NOT NULL,
    type_of_incident VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    police_station VARCHAR(255) NOT NULL,
    guardian_name VARCHAR(255) NOT NULL,
    status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    police_notes TEXT,
    rb_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pf3_number) REFERENCES patients(pf3_number)
);

-- Medical Reports table
CREATE TABLE medical_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pf3_number VARCHAR(20) NOT NULL,
    doctor_id INT NOT NULL,
    injury_type VARCHAR(100) NOT NULL,
    severity VARCHAR(50) NOT NULL,
    patient_condition TEXT NOT NULL,
    medical_findings TEXT NOT NULL,
    recommendations TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pf3_number) REFERENCES patients(pf3_number),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pf3_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pf3_number) REFERENCES patients(pf3_number)
);

-- Audit Logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'doctor', 'police') NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT INTO admins (username, email, phone, password) VALUES ('admin', 'admin@gmail.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- Password: Admin@123

-- Add status column to doctors table
ALTER TABLE doctors ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';

-- Add status column to police_officers table
ALTER TABLE police_officers ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';

-- Add status column to admins table
ALTER TABLE admins ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';

-- Add last_application_date column to patients table
ALTER TABLE patients ADD COLUMN last_application_date DATETIME DEFAULT CURRENT_TIMESTAMP;