<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    // Validate
    $errors = [];
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
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
        $redirect = ($type === 'doctor') ? 'register_doctor.php' : 'register_police.php';
        header("Location: $redirect");
        exit;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        if ($type === 'doctor') {
            $stmt = $pdo->prepare("INSERT INTO doctors (first_name, last_name, position, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $position, $email, $phone, $hashed_password]);
            $redirect = 'manage_users.php?success=doctor';
            $log_action = "Registered Doctor: $first_name $last_name";
        } elseif ($type === 'police') {
            $stmt = $pdo->prepare("INSERT INTO police_officers (first_name, last_name, rank, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $rank, $email, $phone, $hashed_password]);
            $redirect = 'manage_users.php?success=police';
            $log_action = "Registered Police Officer: $first_name $last_name";
        } else {
            header('Location: dashboard.php');
            exit;
        }
        
        logAudit($_SESSION['user_id'], 'admin', $log_action, "Email: $email");
        $_SESSION['success_message'] = "User registered successfully!";
        
        header("Location: $redirect");
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        $redirect = ($type === 'doctor') ? 'register_doctor.php' : 'register_police.php';
        header("Location: $redirect");
        exit;
    }
} else {
    header('Location: dashboard.php');
    exit;
}
?>