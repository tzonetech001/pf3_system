<?php
require_once '../includes/db.php';
$pf3 = $_GET['pf3'] ?? '';

if ($pf3) {
    // Insert notification or update status, but for simplicity, just redirect with message
    $stmt = $pdo->prepare("INSERT INTO notifications (pf3_number, message, type) VALUES (?, 'Review requested by patient', 'review_request')");
    $stmt->execute([$pf3]);
    header('Location: view_status.php?pf3=' . $pf3 . '&msg=review_requested');
} else {
    header('Location: ../index.php');
}
?>