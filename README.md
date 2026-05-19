# Integrated Digital PF3 Coordination and Medical Reporting System

A comprehensive web-based system for managing PF3 medical-legal processes between patients, hospitals, and police departments.

## Features

- **Patient Portal**: No-login system with PF3 reference numbers
- **Doctor Module**: Medical reporting for approved cases
- **Police Module**: Case approval/rejection with RB number generation
- **Admin Module**: User management and system analytics
- **Secure Authentication**: Role-based access with password hashing
- **Forgot Password**: Step-by-step email and phone verification
- **Multi-language Support**: English (EN) and Kiswahili (SW) with persistence
- **Responsive Design**: Works on PC, tablet, and mobile with collapsible navbar
- **Custom Branding**: Hospital and police logos with green/white theme

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- XAMPP/WAMP for local development

## Installation

1. **Clone/Download** the project to your web server root (e.g., `htdocs/pf3_system`)

2. **Database Setup**:
   - Create a new MySQL database named `pf3_system`
   - Import the `database/pf3_system.sql` file
   - Update database credentials in `includes/db.php` if needed

3. **Web Server Configuration**:
   - Ensure PHP is enabled
   - Set document root to the project folder
   - Enable URL rewriting if using Apache

4. **Permissions**:
   - Ensure `uploads/` directory is writable (chmod 755)

4. **Access the System**:
   - Patient Portal: `http://localhost/pf3_system/`
   - Admin Login: `http://localhost/pf3_system/login.php`
     - Email: admin@gmail.com
     - Password: Admin@123
     - Username: admin (alternative)

## User Roles

### Admin
- Email: admin@gmail.com
- Password: Admin@123
- Responsibilities: Register doctors/police, manage users, view reports

### Doctor
- Login via email/password (registered by admin)
- Search PF3 numbers, view patient details, create medical reports

### Police Officer
- Login via email/password (registered by admin)
- Review pending cases, approve/reject with notes, generate RB numbers

### Patient
- No login required
- Use PF3 number for tracking and continuing applications

## Database Schema

- `admins`: Admin users
- `doctors`: Medical professionals
- `police_officers`: Law enforcement users
- `patients`: Patient basic information
- `pf3_cases`: Case details and status
- `medical_reports`: Doctor's medical findings
- `notifications`: System notifications
- `audit_logs`: Activity logging

## Security Features

- Password hashing with bcrypt
- Prepared statements for SQL injection prevention
- Session-based authentication
- Role-based access control
- Input validation and sanitization

## File Structure

```
pf3_system/
├── admin/          # Admin dashboard and management
├── doctor/         # Doctor interface
├── patient/        # Patient-facing pages
├── police/         # Police officer interface
├── includes/       # Shared PHP functions and DB connection
├── database/       # SQL schema and setup
├── assets/         # CSS, JS, images
├── uploads/        # File uploads (if any)
├── index.php       # Main landing page
├── login.php       # Authentication page
└── README.md       # This file
```

## Development Notes

- Uses PDO for database interactions
- Bootstrap 5 for responsive UI
- JavaScript for client-side interactions
- Follows MVC-like structure

## License

This project is for educational/demonstration purposes.