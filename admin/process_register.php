<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'] ?? '123456'; // Default password
    
    // Validate
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    
    // Validate phone number format (9 digits starting with 6 or 7)
    if (!preg_match('/^[67][0-9]{8}$/', $phone)) {
        $errors[] = "Invalid phone number format. Must be 9 digits starting with 6 or 7.";
    }
    
    // Check if email exists
    if ($type === 'doctor') {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "Email already registered as doctor";
        
        $position = trim($_POST['position']);
        if (empty($position)) $errors[] = "Position is required";
        
    } elseif ($type === 'police') {
        $stmt = $pdo->prepare("SELECT id FROM police_officers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "Email already registered as police officer";
        
        $rank = trim($_POST['rank']);
        if (empty($rank)) $errors[] = "Rank is required";
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header('Location: manage_users.php');
        exit;
    }
    
    // Convert phone to international format (255 + 9 digits)
    $phone_clean = preg_replace('/^0+/', '', $phone);
    $phone_clean = preg_replace('/^255/', '', $phone_clean);
    $phone_international = '255' . $phone_clean;
    
    // Hash the password (default: 123456)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        if ($type === 'doctor') {
            $stmt = $pdo->prepare("
                INSERT INTO doctors (first_name, last_name, position, email, phone, password) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$first_name, $last_name, $position, $email, $phone_international, $hashed_password]);
            $log_action = "Registered Doctor: $first_name $last_name";
            
        } elseif ($type === 'police') {
            $stmt = $pdo->prepare("
                INSERT INTO police_officers (first_name, last_name, rank, email, phone, password) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$first_name, $last_name, $rank, $email, $phone_international, $hashed_password]);
            $log_action = "Registered Police Officer: $first_name $last_name";
            
        } else {
            header('Location: dashboard.php');
            exit;
        }
        
        logAudit($_SESSION['user_id'], 'admin', $log_action, "Email: $email, Phone: $phone_international");
        $_SESSION['success_message'] = "User registered successfully! Default password is: 123456";
        
        header('Location: manage_users.php');
        exit;
        
    } catch (PDOException $e) {
        error_log("Process register error: " . $e->getMessage());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: manage_users.php');
        exit;
    }
} else {
    header('Location: dashboard.php');
    exit;
}
?>