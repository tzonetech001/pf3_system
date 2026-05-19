<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['pf3_number'])) {
    $pf3_number = $_SESSION['pf3_number'];
    $type_of_incident = $_POST['type_of_incident'];
    $description = $_POST['description'];
    $police_station = $_POST['police_station'];
    $guardian_name = $_POST['guardian_name'];

    // Insert into pf3_cases
    $stmt = $pdo->prepare("INSERT INTO pf3_cases (pf3_number, type_of_incident, description, police_station, guardian_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$pf3_number, $type_of_incident, $description, $police_station, $guardian_name]);

    // Clear session
    unset($_SESSION['pf3_number']);

    header('Location: application_submitted.php');
    exit;
} else {
    header('Location: ../index.php');
    exit;
}
?>