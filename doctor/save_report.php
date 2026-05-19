<?php
session_start();
require_once '../includes/db.php';
requireLogin('doctor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pf3 = $_POST['pf3'];
    $injury_type = $_POST['injury_type'];
    $severity = $_POST['severity'];
    $condition = $_POST['condition'];
    $findings = $_POST['findings'];
    $recommendations = $_POST['recommendations'];

    $stmt = $pdo->prepare("INSERT INTO medical_reports (pf3_number, doctor_id, injury_type, severity, patient_condition, medical_findings, recommendations) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$pf3, $_SESSION['user_id'], $injury_type, $severity, $condition, $findings, $recommendations]);

    logAudit($_SESSION['user_id'], 'doctor', 'Medical report added', 'PF3: ' . $pf3);

    header('Location: view_patient.php?pf3=' . $pf3 . '&success=1');
    exit;
}
?>