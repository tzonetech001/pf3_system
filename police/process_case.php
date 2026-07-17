<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/sms_helper.php';
requireLogin('police');

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $pf3 = isset($_POST['pf3']) ? trim($_POST['pf3']) : '';
    $decision = isset($_POST['decision']) ? trim($_POST['decision']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // ============================================
    // DEBUG - Log all incoming data
    // ============================================
    error_log("========== PROCESS CASE ==========");
    error_log("PF3: " . $pf3);
    error_log("Decision: " . $decision);
    error_log("Notes Length: " . strlen($notes));
    error_log("Notes: " . substr($notes, 0, 200));
    error_log("===================================");
    
    // Validate PF3
    if (empty($pf3)) {
        $_SESSION['error_message'] = "PF3 number is required.";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    // Validate Decision
    if (empty($decision)) {
        $_SESSION['error_message'] = "Please select a decision (Approve or Reject).";
        header('Location: view_case.php?pf3=' . $pf3);
        exit;
    }
    
    // Get case details
    $case = getCaseByPF3($pf3);
    if (!$case) {
        $_SESSION['error_message'] = "Case not found!";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    error_log("Current status in database: " . $case['status']);
    
    // Check if case is already processed
    if ($case['status'] !== 'PENDING') {
        $_SESSION['error_message'] = "This case has already been processed! Current status: " . $case['status'];
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    // Get patient details
    $patient = getPatientByPF3($pf3);
    if (!$patient) {
        $_SESSION['error_message'] = "Patient not found!";
        header('Location: cases.php?status=PENDING');
        exit;
    }
    
    try {
        // ============================================
        // APPROVE CASE
        // ============================================
        if ($decision === 'APPROVE') {
            
            // Generate RB number
            $rb_number = generateRBNumber();
            
            // Update database
            $sql = "UPDATE pf3_cases SET status = 'APPROVED', police_notes = ?, rb_number = ?, updated_at = NOW() WHERE pf3_number = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$notes, $rb_number, $pf3]);
            
            if (!$result) {
                error_log("APPROVE FAILED for PF3: $pf3");
                $_SESSION['error_message'] = "Failed to approve case. Please try again.";
                header('Location: view_case.php?pf3=' . $pf3);
                exit;
            }
            
            error_log("APPROVED successfully: $pf3, RB: $rb_number");
            
            // Send SMS to patient
            $sms_message = "PF3 SYS: Mteja " . $patient['full_name'] . ", maombi yako #$pf3 yamekubaliwa. Namba ya RB: $rb_number. Nenda hospitali kwa daktari. Asante!";
            if (strlen($sms_message) > 159) {
                $sms_message = "PF3 SYS: Maombi #$pf3 yamekubaliwa. RB: $rb_number. Nenda hospitali. Asante!";
            }
            $sms_result = sendSMS($patient['phone'], $sms_message);
            
            // Create notification
            $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, ?, 'APPROVAL')");
            $stmt->execute([$pf3, "Case PF3: $pf3 has been APPROVED. RB Number: $rb_number"]);
            
            // Log audit
            logAudit($_SESSION['user_id'], 'police', 'Case Approved', "PF3: $pf3, RB: $rb_number");
            
            $_SESSION['success_message'] = "✅ Case approved successfully! RB Number: $rb_number";
            header('Location: cases.php?status=APPROVED');
            exit;
        }
        
        // ============================================
        // REJECT CASE
        // ============================================
        elseif ($decision === 'REJECT') {
            
            // Validate rejection reason
            if (empty($notes) || strlen($notes) < 3) {
                $_SESSION['error_message'] = "Please provide a detailed reason for rejection (at least 3 characters).";
                header('Location: view_case.php?pf3=' . $pf3);
                exit;
            }
            
            error_log("Processing REJECT for PF3: $pf3");
            
            // ============================================
            // UPDATE DATABASE - REJECT
            // ============================================
            $sql = "UPDATE pf3_cases SET status = 'REJECTED', police_notes = ?, updated_at = NOW() WHERE pf3_number = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$notes, $pf3]);
            
            error_log("REJECT SQL: " . $sql);
            error_log("REJECT Notes: " . substr($notes, 0, 100));
            error_log("REJECT PF3: " . $pf3);
            error_log("REJECT Result: " . ($result ? 'TRUE' : 'FALSE'));
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("REJECT PDO Error: " . print_r($errorInfo, true));
                $_SESSION['error_message'] = "Database error: Failed to reject case. " . $errorInfo[2];
                header('Location: view_case.php?pf3=' . $pf3);
                exit;
            }
            
            // ============================================
            // VERIFY UPDATE WAS SUCCESSFUL
            // ============================================
            $verify_stmt = $pdo->prepare("SELECT status FROM pf3_cases WHERE pf3_number = ?");
            $verify_stmt->execute([$pf3]);
            $updated_case = $verify_stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Verified status after update: " . ($updated_case ? $updated_case['status'] : 'NOT FOUND'));
            
            if (!$updated_case || $updated_case['status'] !== 'REJECTED') {
                error_log("REJECT VERIFICATION FAILED for PF3: $pf3");
                $_SESSION['error_message'] = "Failed to update case status to REJECTED. Please try again.";
                header('Location: view_case.php?pf3=' . $pf3);
                exit;
            }
            
            error_log("✅ REJECTED successfully: $pf3");
            
            // ============================================
            // SEND SMS TO PATIENT - REJECTED
            // ============================================
            $rejection_reason = substr($notes, 0, 80);
            $sms_message = "PF3 SYS: Mteja " . $patient['full_name'] . ", maombi yako #$pf3 yamekataliwa. Sababu: $rejection_reason. Wasiliana na polisi. Asante!";
            if (strlen($sms_message) > 159) {
                $sms_message = "PF3 SYS: Maombi #$pf3 yamekataliwa. Wasiliana na polisi. Asante!";
            }
            $sms_result = sendSMS($patient['phone'], $sms_message);
            
            if ($sms_result['success']) {
                error_log("SMS SENT to patient {$patient['phone']} - REJECTED");
            } else {
                error_log("SMS FAILED to patient {$patient['phone']}: " . $sms_result['message']);
            }
            
            // ============================================
            // CREATE NOTIFICATION
            // ============================================
            $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, ?, 'REJECTION')");
            $stmt->execute([$pf3, "Case PF3: $pf3 has been REJECTED. Reason: " . substr($notes, 0, 200)]);
            
            // ============================================
            // LOG AUDIT
            // ============================================
            logAudit($_SESSION['user_id'], 'police', 'Case Rejected', "PF3: $pf3, Reason: " . substr($notes, 0, 50));
            
            $_SESSION['error_message'] = "❌ Case rejected successfully. SMS sent to patient.";
            header('Location: cases.php?status=REJECTED');
            exit;
        }
        
        // ============================================
        // INVALID DECISION
        // ============================================
        else {
            $_SESSION['error_message'] = "Invalid decision! Please select Approve or Reject.";
            header('Location: view_case.php?pf3=' . $pf3);
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("PDOException in process_case: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: view_case.php?pf3=' . $pf3);
        exit;
    } catch (Exception $e) {
        error_log("Exception in process_case: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
        header('Location: view_case.php?pf3=' . $pf3);
        exit;
    }
    
} else {
    // Not POST request
    header('Location: dashboard.php');
    exit;
}
?>