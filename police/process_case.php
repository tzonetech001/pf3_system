<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/sms_helper.php';
requireLogin('police');

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header('Location: dashboard.php');
    exit;
}

// Get form data
$action = $_POST['action'] ?? '';
$pf3_number = trim($_POST['pf3_number'] ?? '');
$police_notes = trim($_POST['police_notes'] ?? '');
$rb_number = trim($_POST['rb_number'] ?? '');

// Validate inputs
if (empty($action) || empty($pf3_number)) {
    $_SESSION['error_message'] = "Missing required fields.";
    header('Location: cases.php?status=PENDING');
    exit;
}

if (!in_array($action, ['approve', 'reject'])) {
    $_SESSION['error_message'] = "Invalid action.";
    header('Location: cases.php?status=PENDING');
    exit;
}

// Validate action-specific fields
if ($action === 'approve') {
    if (empty($rb_number)) {
        $_SESSION['error_message'] = "RB number is required for approval.";
        header('Location: view_case.php?pf3=' . $pf3_number);
        exit;
    }
}

if ($action === 'reject') {
    // If police notes are empty, use default message
    if (empty($police_notes)) {
        $police_notes = "Application rejected by police officer.";
    }
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // ============================================================
    // STEP 1: Get the case details and verify it's PENDING
    // ============================================================
    $stmt = $pdo->prepare("
        SELECT c.*, p.full_name, p.phone, p.guardian_phone 
        FROM pf3_cases c 
        JOIN patients p ON c.pf3_number = p.pf3_number 
        WHERE c.pf3_number = ?
    ");
    $stmt->execute([$pf3_number]);
    $case = $stmt->fetch();
    
    if (!$case) {
        throw new Exception("Case not found.");
    }
    
    // Check if case is already processed
    if ($case['status'] !== 'PENDING') {
        throw new Exception("This case has already been processed. Current status: " . $case['status']);
    }
    
    // ============================================================
    // STEP 2: Update the case status in the database
    // ============================================================
    $new_status = strtoupper($action);
    $updated_at = date('Y-m-d H:i:s');
    
    if ($action === 'approve') {
        // APPROVE: Update status to APPROVED, set RB number
        $sql = "UPDATE pf3_cases SET 
                status = 'APPROVED',
                police_notes = :police_notes,
                rb_number = :rb_number,
                updated_at = :updated_at
                WHERE pf3_number = :pf3_number";
        
        $params = [
            'police_notes' => $police_notes,
            'rb_number' => $rb_number,
            'updated_at' => $updated_at,
            'pf3_number' => $pf3_number
        ];
    } else {
        // REJECT: Update status to REJECTED
        $sql = "UPDATE pf3_cases SET 
                status = 'REJECTED',
                police_notes = :police_notes,
                rb_number = NULL,
                updated_at = :updated_at
                WHERE pf3_number = :pf3_number";
        
        $params = [
            'police_notes' => $police_notes,
            'updated_at' => $updated_at,
            'pf3_number' => $pf3_number
        ];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Verify the update was successful
    if ($stmt->rowCount() === 0) {
        throw new Exception("Failed to update case status. Please try again.");
    }
    
    // ============================================================
    // STEP 3: Log the action in audit_logs
    // ============================================================
    $action_log = $action === 'approve' ? "Approved Case - Status changed to APPROVED" : "Rejected Case - Status changed to REJECTED";
    $details = "PF3: $pf3_number, Patient: {$case['full_name']}, " . 
               ($action === 'approve' ? "RB: $rb_number" : "Reason: $police_notes") .
               " | Status updated from PENDING to " . strtoupper($action);
    
    logAudit($_SESSION['user_id'], 'police', $action_log, $details);
    
    // ============================================================
    // STEP 4: Add notification to notifications table
    // ============================================================
    $notification_message = $action === 'approve' 
        ? "Your PF3 application #$pf3_number has been APPROVED. RB Number: $rb_number. Please proceed to the hospital for medical examination."
        : "Your PF3 application #$pf3_number has been REJECTED. Reason: $police_notes. Please contact the police station for more information.";
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (pf3_number, message, type, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$pf3_number, $notification_message, $new_status]);
    
    // ============================================================
    // STEP 5: Send SMS notification to patient (AUTOMATIC)
    // ============================================================
    $patient_phone = $case['phone'];
    $patient_name = $case['full_name'];
    
    // Send SMS using the helper function
    $sms_result = sendPF3StatusUpdateSMS(
        $patient_phone,
        $patient_name,
        $pf3_number,
        $new_status,
        $police_notes,
        $action === 'approve' ? $rb_number : ''
    );
    
    // Log SMS result
    if ($sms_result['success']) {
        error_log("SMS sent successfully to $patient_phone for PF3 $pf3_number - Status: $new_status");
        
        // Add SMS notification to database
        $stmt = $pdo->prepare("
            INSERT INTO notifications (pf3_number, message, type, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $pf3_number,
            "SMS sent to patient: " . $patient_phone . " - Status: $new_status",
            'SMS'
        ]);
    } else {
        error_log("SMS FAILED for PF3 $pf3_number: " . $sms_result['message']);
        
        // Log failed SMS
        $stmt = $pdo->prepare("
            INSERT INTO notifications (pf3_number, message, type, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $pf3_number,
            "SMS failed to send to $patient_phone: " . $sms_result['message'],
            'ERROR'
        ]);
    }
    
    // ============================================================
    // STEP 6: Send SMS to guardian if phone exists
    // ============================================================
    if (!empty($case['guardian_phone']) && $case['guardian_phone'] !== $patient_phone) {
        $guardian_phone = cleanPhoneNumber($case['guardian_phone']);
        
        if ($action === 'approve') {
            $guardian_message = "PF3 SYS: Mlezi wa {$case['full_name']}, maombi #$pf3_number yamekubaliwa. RB: $rb_number. Mlezi anapaswa kuandamana na mgonjwa hospitalini.";
        } else {
            $guardian_message = "PF3 SYS: Mlezi wa {$case['full_name']}, maombi #$pf3_number yamekataliwa. Wasiliana na polisi kwa maelezo.";
        }
        
        // Shorten guardian message if needed
        if (strlen($guardian_message) > 155) {
            $guardian_message = $action === 'approve'
                ? "PF3 SYS: Mlezi wa {$case['full_name']}, maombi #$pf3_number yamekubaliwa. RB: $rb_number."
                : "PF3 SYS: Mlezi wa {$case['full_name']}, maombi #$pf3_number yamekataliwa.";
        }
        
        $guardian_sms = sendSMS($guardian_phone, $guardian_message);
        if ($guardian_sms['success']) {
            error_log("Guardian SMS sent to $guardian_phone for PF3 $pf3_number");
            
            // Log guardian SMS
            $stmt = $pdo->prepare("
                INSERT INTO notifications (pf3_number, message, type, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([
                $pf3_number,
                "Guardian SMS sent to: " . $guardian_phone,
                'SMS'
            ]);
        }
    }
    
    // ============================================================
    // STEP 7: Commit transaction
    // ============================================================
    $pdo->commit();
    
    // ============================================================
    // STEP 8: Set success message and redirect
    // ============================================================
    $action_display = $action === 'approve' ? 'APPROVED' : 'REJECTED';
    $success_msg = "Case #$pf3_number has been " . strtoupper($action) . " successfully!";
    
    if ($sms_result['success']) {
        $success_msg .= " SMS sent to patient.";
    } else {
        $success_msg .= " SMS could not be sent: " . $sms_result['message'];
    }
    
    $_SESSION['success_message'] = $success_msg;
    header('Location: view_case.php?pf3=' . $pf3_number);
    exit;
    
} catch (Exception $e) {
    // ============================================================
    // ERROR HANDLING - Rollback transaction
    // ============================================================
    $pdo->rollBack();
    
    error_log("Process Case Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: view_case.php?pf3=' . $pf3_number);
    exit;
    
} catch (PDOException $e) {
    // ============================================================
    // DATABASE ERROR - Rollback transaction
    // ============================================================
    $pdo->rollBack();
    
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header('Location: view_case.php?pf3=' . $pf3_number);
    exit;
}
?>