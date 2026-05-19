<?php
require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'check_email') {
    $email = $data['email'];
    $tables = ['admins', 'doctors', 'police_officers'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
} elseif ($action === 'check_phone') {
    $email = $data['email'];
    $phone = $data['phone'];
    $tables = ['admins', 'doctors', 'police_officers'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ? AND phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Phone number does not match.']);
} elseif ($action === 'reset_password') {
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $tables = ['admins', 'doctors', 'police_officers'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE email = ?");
        $stmt->execute([$password, $email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Failed to reset password.']);
}
?>