<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if ($type === 'doctor') {
        $position = $_POST['position'];
        $stmt = $pdo->prepare("INSERT INTO doctors (first_name, last_name, position, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $position, $email, $phone, $password]);
        $redirect = 'register_doctor.php';
    } elseif ($type === 'police') {
        $rank = $_POST['rank'];
        $stmt = $pdo->prepare("INSERT INTO police_officers (first_name, last_name, rank, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $rank, $email, $phone, $password]);
        $redirect = 'register_police.php';
    }

    logAudit($_SESSION['user_id'], 'admin', 'Registered ' . $type, $first_name . ' ' . $last_name);

    header('Location: ' . $redirect . '?success=1');
    exit;
}
?>