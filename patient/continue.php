<?php
require_once '../includes/db.php';
$pf3 = $_GET['pf3'] ?? '';

$patient = getPatientByPF3($pf3);
$case = getCaseByPF3($pf3);

if (!$patient) {
    header('Location: continue_application.php?error=invalid');
    exit;
}

if ($case) {
    // Already submitted, redirect to status
    header('Location: view_status.php?pf3=' . $pf3);
} else {
    // Continue to step 2
    session_start();
    $_SESSION['pf3_number'] = $pf3;
    header('Location: step2.php');
}
exit;
?>