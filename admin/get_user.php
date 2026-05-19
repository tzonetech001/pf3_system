<?php
session_start();
require_once '../includes/db.php';
requireLogin('admin');

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    if ($type === 'doctor') {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, position, email, phone FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'user' => $user]);
    } elseif ($type === 'police') {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, rank, email, phone FROM police_officers WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'user' => $user]);
    } elseif ($type === 'admin') {
        $stmt = $pdo->prepare("SELECT id, username, email, phone FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>