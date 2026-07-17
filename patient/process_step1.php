<?php
session_start();
require_once '../includes/db.php';

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $gender = $_POST['gender'];
    $age = (int)$_POST['age'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $guardian_phone = trim($_POST['guardian_phone']);
    $incident_date_time = $_POST['incident_date_time'];

    // Validate phone numbers
    $errors = [];

    // Validate main phone (required) - should be 9 digits starting with 6 or 7
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[67][0-9]{8}$/', $phone)) {
        $errors[] = "Invalid phone number format. Must be 9 digits starting with 6 or 7.";
    }

    // Validate guardian phone (optional)
    if (!empty($guardian_phone)) {
        if (!preg_match('/^[67][0-9]{8}$/', $guardian_phone)) {
            $errors[] = "Invalid guardian phone number format. Must be 9 digits starting with 6 or 7.";
        }
    }

    // Validate other fields
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($gender)) $errors[] = "Gender is required";
    if (empty($age) || $age < 0 || $age > 150) $errors[] = "Valid age is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($incident_date_time)) $errors[] = "Incident date and time is required";

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header('Location: create_pf3.php');
        exit;
    }

    // Convert phone numbers to international format
    $phone_clean = preg_replace('/^0+/', '', $phone);
    $phone_clean = preg_replace('/^255/', '', $phone_clean);
    $phone_international = '255' . $phone_clean;

    $guardian_phone_international = '';
    if (!empty($guardian_phone)) {
        $guardian_clean = preg_replace('/^0+/', '', $guardian_phone);
        $guardian_clean = preg_replace('/^255/', '', $guardian_clean);
        $guardian_phone_international = '255' . $guardian_clean;
    }

    // Generate unique PF3 number
    do {
        $pf3_number = generatePF3Number();
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE pf3_number = ?");
        $stmt->execute([$pf3_number]);
    } while ($stmt->fetch());

    // Insert into patients
    try {
        $stmt = $pdo->prepare("
            INSERT INTO patients 
            (pf3_number, full_name, gender, age, address, phone, guardian_phone, incident_date_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $pf3_number, 
            $full_name, 
            $gender, 
            $age, 
            $address, 
            $phone_international, 
            $guardian_phone_international, 
            $incident_date_time
        ]);

        if ($result) {
            // Store PF3 in session for next step
            $_SESSION['pf3_number'] = $pf3_number;
            
            // Redirect to step2.php
            header('Location: step2.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to save patient information.";
            header('Location: create_pf3.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Process step1 error: " . $e->getMessage());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: create_pf3.php');
        exit;
    }
} else {
    header('Location: create_pf3.php');
    exit;
}
?>