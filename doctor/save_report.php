<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pf3 = $_POST['pf3'];
    $injury_type = trim($_POST['injury_type']);
    $severity = $_POST['severity'];
    $condition = trim($_POST['condition']);
    $findings = trim($_POST['findings']);
    $recommendations = trim($_POST['recommendations']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($pf3)) $errors[] = "PF3 number is required";
    if (empty($injury_type)) $errors[] = "Injury type is required";
    if (empty($severity)) $errors[] = "Severity level is required";
    if (empty($condition)) $errors[] = "Patient condition is required";
    if (empty($findings)) $errors[] = "Medical findings are required";
    if (empty($recommendations)) $errors[] = "Recommendations are required";
    
    // Check if report already exists
    $stmt = $pdo->prepare("SELECT id FROM medical_reports WHERE pf3_number = ? AND doctor_id = ?");
    $stmt->execute([$pf3, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "A medical report already exists for this PF3 number.";
        header('Location: view_patient.php?pf3=' . $pf3);
        exit;
    }
    
    // Check if case is approved
    $case = getCaseByPF3($pf3);
    if (!$case || $case['status'] !== 'APPROVED') {
        $_SESSION['error_message'] = "This PF3 case is not approved yet. Medical reports can only be created for approved cases.";
        header('Location: search_pf3.php');
        exit;
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO medical_reports (pf3_number, doctor_id, injury_type, severity, patient_condition, medical_findings, recommendations) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$pf3, $_SESSION['user_id'], $injury_type, $severity, $condition, $findings, $recommendations]);
            
            logAudit($_SESSION['user_id'], 'doctor', 'Medical report added', 'PF3: ' . $pf3);
            
            // Create notification
            $message = "Medical report has been added for PF3: $pf3";
            $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, ?, 'MEDICAL_REPORT')");
            $stmt->execute([$pf3, $message]);
            
            header('Location: view_patient.php?pf3=' . $pf3 . '&success=1');
            exit;
            
        } catch (PDOException $e) {
            error_log("Save report error: " . $e->getMessage());
            $_SESSION['error_message'] = "An error occurred while saving the report. Please try again.";
            header('Location: view_patient.php?pf3=' . $pf3);
            exit;
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header('Location: view_patient.php?pf3=' . $pf3);
        exit;
    }
} else {
    header('Location: search_pf3.php');
    exit;
}
?>