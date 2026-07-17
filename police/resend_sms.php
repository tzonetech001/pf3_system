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

// Send appropriate SMS based on status
if ($case['status'] === 'APPROVED') {
    $rb_number = $case['rb_number'] ?? 'N/A';
    $sms_message = "PF3 SYS: Mteja " . $patient['full_name'] . ", maombi yako #$pf3 yamekubaliwa. Namba ya RB: $rb_number. Tafadhali nenda hospitali na uonyeshe namba hii kwa daktari kwa uchunguzi. Asante!";
    
    if (strlen($sms_message) > 159) {
        $name_short = strlen($patient['full_name']) > 10 ? substr($patient['full_name'], 0, 10) : $patient['full_name'];
        $sms_message = "PF3 SYS: $name_short, maombi #$pf3 yamekubaliwa. RB: $rb_number. Nenda hospitali kwa daktari. Asante!";
    }
    
    $result = sendSMS($patient['phone'], $sms_message);
    $action = "Resent APPROVED SMS";
    
} elseif ($case['status'] === 'REJECTED') {
    $reason = substr($case['police_notes'] ?? 'No reason provided', 0, 100);
    $sms_message = "PF3 SYS: Mteja " . $patient['full_name'] . ", maombi yako #$pf3 yamekataliwa. Sababu: $reason. Tafadhali wasiliana na kituo cha polisi kwa maelezo zaidi. Asante!";
    
    if (strlen($sms_message) > 159) {
        $name_short = strlen($patient['full_name']) > 8 ? substr($patient['full_name'], 0, 8) : $patient['full_name'];
        $reason_short = strlen($reason) > 40 ? substr($reason, 0, 40) . '...' : $reason;
        $sms_message = "PF3 SYS: $name_short, maombi #$pf3 yamekataliwa. Sababu: $reason_short. Wasiliana na polisi. Asante!";
    }
    
    $result = sendSMS($patient['phone'], $sms_message);
    $action = "Resent REJECTED SMS";
    
} else {
    $_SESSION['error_message'] = "Case is still PENDING. Cannot resend SMS.";
    header('Location: view_case.php?pf3=' . $pf3);
    exit;
}

// Log action
logAudit($_SESSION['user_id'], 'police', $action, "PF3: $pf3, Phone: {$patient['phone']}");

if ($result['success']) {
    $_SESSION['success_message'] = "SMS resent successfully to {$patient['phone']}";
    error_log("SMS resent to {$patient['phone']} for PF3 $pf3 - $action");
} else {
    $_SESSION['error_message'] = "Failed to resend SMS: " . $result['message'];
    error_log("SMS resend failed for PF3 $pf3: " . $result['message']);
}

header('Location: view_case.php?pf3=' . $pf3);
exit;
?>