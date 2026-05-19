<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $guardian_phone = $_POST['guardian_phone'];
    $incident_date_time = $_POST['incident_date_time'];

    // Generate unique PF3 number
    do {
        $pf3_number = generatePF3Number();
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE pf3_number = ?");
        $stmt->execute([$pf3_number]);
    } while ($stmt->fetch());

    // Insert into patients
    $stmt = $pdo->prepare("INSERT INTO patients (pf3_number, full_name, gender, age, address, phone, guardian_phone, incident_date_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$pf3_number, $full_name, $gender, $age, $address, $phone, $guardian_phone, $incident_date_time]);

    // Store PF3 in session for next step
    session_start();
    $_SESSION['pf3_number'] = $pf3_number;

    header('Location: step2.php');
    exit;
}
?>