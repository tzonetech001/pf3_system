<?php
session_start();
require_once '../includes/db.php';
requireLogin('police');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pf3 = $_POST['pf3'] ?? '';
    $decision = $_POST['decision'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate inputs
    if (empty($pf3) || empty($decision)) {
        $_SESSION['error_message'] = "Invalid request. Missing required fields.";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    // Get case details
    $case = getCaseByPF3($pf3);
    if (!$case) {
        $_SESSION['error_message'] = "Case not found!";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    if ($case['status'] !== 'PENDING') {
        $_SESSION['error_message'] = "This case has already been processed!";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    try {
        if ($decision === 'APPROVE') {
            $status = 'APPROVED';
            $rb_number = generateRBNumber();
            
            $stmt = $pdo->prepare("
                UPDATE pf3_cases 
                SET status = ?, police_notes = ?, rb_number = ?, updated_at = NOW() 
                WHERE pf3_number = ?
            ");
            $stmt->execute([$status, $notes, $rb_number, $pf3]);
            
            // Create notification for hospital
            $message = "Case PF3: $pf3 has been APPROVED. RB Number: $rb_number";
            $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, ?, 'APPROVAL')");
            $stmt->execute([$pf3, $message]);
            
            $_SESSION['success_message'] = "Case approved successfully! RB Number: $rb_number";
            logAudit($_SESSION['user_id'], 'police', 'Case Approved', "PF3: $pf3, RB: $rb_number");
            
        } elseif ($decision === 'REJECT') {
            if (empty($notes)) {
                $_SESSION['error_message'] = "Please provide a reason for rejection.";
                header('Location: view_case.php?pf3=' . $pf3);
                exit;
            }
            
            $status = 'REJECTED';
            $stmt = $pdo->prepare("
                UPDATE pf3_cases 
                SET status = ?, police_notes = ?, updated_at = NOW() 
                WHERE pf3_number = ?
            ");
            $stmt->execute([$status, $notes, $pf3]);
            
            // Create notification for hospital
            $message = "Case PF3: $pf3 has been REJECTED. Reason: " . substr($notes, 0, 200);
            $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, ?, 'REJECTION')");
            $stmt->execute([$pf3, $message]);
            
            $_SESSION['error_message'] = "Case rejected successfully.";
            logAudit($_SESSION['user_id'], 'police', 'Case Rejected', "PF3: $pf3");
        } else {
            $_SESSION['error_message'] = "Invalid decision!";
            header('Location: view_case.php?pf3=' . $pf3);
            exit;
        }
        
        header('Location: cases.php?status=' . $status);
        exit;
        
    } catch (PDOException $e) {
        error_log("Process case error: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while processing the case. Please try again.";
        header('Location: view_case.php?pf3=' . $pf3);
        exit;
    }
} else {
    header('Location: dashboard.php');
    exit;
}
?>