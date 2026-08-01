<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/sms_helper.php';
requireLogin('police');

// Set Tanzania timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

$pf3 = $_GET['pf3'] ?? '';

if (empty($pf3)) {
    $_SESSION['error_message'] = "Invalid PF3 number.";
    header('Location: dashboard.php');
    exit;
}

// Get case details
$case = getCaseByPF3($pf3);
if (!$case) {
    $_SESSION['error_message'] = "Case not found!";
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

// Check if case is processed (approved or rejected)
if ($case['status'] === 'PENDING') {
    $_SESSION['error_message'] = "Case is still PENDING. Cannot resend SMS. Please approve or reject first.";
    header('Location: view_case.php?pf3=' . $pf3);
    exit;
}

// Build SMS message based on status - SAME FORMAT AS process_case.php
if ($case['status'] === 'APPROVED') {
    $rb_number = $case['rb_number'] ?? 'N/A';
    $patient_name = $patient['full_name'];
    
    // Same format as process_case.php
    $sms_text = "PF3 SYS: Dear $patient_name, application #$pf3 APPROVED. RB Number: $rb_number. Visit hospital. Thank you!";
    
    // Limit to 155 characters
    if (strlen($sms_text) > 155) {
        $name_short = strlen($patient_name) > 8 ? substr($patient_name, 0, 8) : $patient_name;
        $sms_text = "PF3 SYS: $name_short, app #$pf3 APPROVED. RB: $rb_number. Visit hospital. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $sms_text = "PF3 SYS: App #$pf3 APPROVED. RB: $rb_number. Visit hospital. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $sms_text = "PF3 SYS: #$pf3 APPROVED. RB: $rb_number. Visit hospital. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $sms_text = "PF3 SYS: #$pf3 APPROVED. RB: $rb_number. Thank you!";
    }
    
    $action = "Resent APPROVED SMS";
    
} elseif ($case['status'] === 'REJECTED') {
    $reason = $case['police_notes'] ?? 'No reason provided';
    $patient_name = $patient['full_name'];
    
    // Same format as process_case.php
    $reason_short = strlen($reason) > 35 ? substr($reason, 0, 35) . '...' : $reason;
    $sms_text = "PF3 SYS: Dear $patient_name, application #$pf3 REJECTED. Reason: $reason_short. Contact police. Thank you!";
    
    // Limit to 155 characters
    if (strlen($sms_text) > 155) {
        $name_short = strlen($patient_name) > 8 ? substr($patient_name, 0, 8) : $patient_name;
        $reason_short2 = strlen($reason) > 20 ? substr($reason, 0, 20) . '...' : $reason;
        $sms_text = "PF3 SYS: $name_short, app #$pf3 REJECTED. Reason: $reason_short2. Contact police. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $reason_short3 = strlen($reason) > 10 ? substr($reason, 0, 10) . '...' : $reason;
        $sms_text = "PF3 SYS: #$pf3 REJECTED. Reason: $reason_short3. Contact police. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $sms_text = "PF3 SYS: #$pf3 REJECTED. Contact police. Thank you!";
    }
    if (strlen($sms_text) > 155) {
        $sms_text = "PF3 SYS: #$pf3 REJECTED. Thank you!";
    }
    
    $action = "Resent REJECTED SMS";
    
} else {
    $_SESSION['error_message'] = "Invalid case status.";
    header('Location: view_case.php?pf3=' . $pf3);
    exit;
}

// Clean the message (remove emojis and special chars)
$sms_text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $sms_text);
$sms_text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $sms_text);
$sms_text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $sms_text);
$sms_text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $sms_text);
$sms_text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $sms_text);
$sms_text = str_replace(['*', '_', '~', '`', '|', '>', '<', '✅', '❌', '📱', '📞'], '', $sms_text);
$sms_text = preg_replace('/\s+/', ' ', $sms_text);
$sms_text = trim($sms_text);

// Final fallback
if (empty($sms_text)) {
    $sms_text = "PF3 SYS: Application #$pf3 updated to " . $case['status'] . ". Contact police for details.";
}

// Send SMS
$result = sendSMS($patient['phone'], $sms_text);

// Log action
logAudit($_SESSION['user_id'], 'police', $action, "PF3: $pf3, Phone: {$patient['phone']}");

if ($result['success']) {
    $_SESSION['success_message'] = "SMS resent successfully to {$patient['phone']}";
    error_log("SMS resent to {$patient['phone']} for PF3 $pf3 - $action");
    error_log("SMS Content: $sms_text");
} else {
    $_SESSION['error_message'] = "Failed to resend SMS: " . $result['message'];
    error_log("SMS resend failed for PF3 $pf3: " . $result['message']);
}

header('Location: view_case.php?pf3=' . $pf3);
exit;
?>