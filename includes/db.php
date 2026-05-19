<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pf3_system');
define('DB_USER', 'root'); // Change as needed
define('DB_PASS', ''); // Change as needed

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to generate PF3 number
function generatePF3Number() {
    return 'PF3-' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to generate RB number
function generateRBNumber() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $rb = '';
    for ($i = 0; $i < 6; $i++) {
        $rb .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return 'RB-' . $rb;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Function to require login
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    if ($role && $_SESSION['user_type'] !== $role) {
        header('Location: ../unauthorized.php');
        exit;
    }
}

// Function to log audit
function logAudit($user_id, $user_type, $action, $details = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, user_type, action, details) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $user_type, $action, $details]);
}

// Function to get patient by PF3
function getPatientByPF3($pf3) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE pf3_number = ?");
    $stmt->execute([$pf3]);
    return $stmt->fetch();
}

// Function to get case by PF3
function getCaseByPF3($pf3) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pf3_cases WHERE pf3_number = ?");
    $stmt->execute([$pf3]);
    return $stmt->fetch();
}

// Function to get medical report by PF3
function getMedicalReportByPF3($pf3) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT mr.*, d.first_name, d.last_name FROM medical_reports mr JOIN doctors d ON mr.doctor_id = d.id WHERE mr.pf3_number = ?");
    $stmt->execute([$pf3]);
    return $stmt->fetch();
}
?>