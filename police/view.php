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

// Check if case is APPROVED or REJECTED (already processed)
if ($case['status'] === 'PENDING') {
    $_SESSION['error_message'] = "Case is still PENDING. Cannot resend SMS. Please approve or reject first.";
    header('Location: view_case.php?pf3=' . $pf3);
    exit;
}

// Send appropriate SMS based on status
if ($case['status'] === 'APPROVED') {
    $rb_number = $case['rb_number'] ?? 'N/A';
    
    // Use the same SMS helper function
    $result = sendPF3StatusUpdateSMS(
        $patient['phone'],
        $patient['full_name'],
        $pf3,
        'APPROVED',
        '',
        $rb_number
    );
    $action = "Resent APPROVED SMS";
    
} elseif ($case['status'] === 'REJECTED') {
    $reason = $case['police_notes'] ?? 'No reason provided';
    
    // Use the same SMS helper function
    $result = sendPF3StatusUpdateSMS(
        $patient['phone'],
        $patient['full_name'],
        $pf3,
        'REJECTED',
        $reason,
        ''
    );
    $action = "Resent REJECTED SMS";
    
} else {
    $_SESSION['error_message'] = "Invalid case status.";
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