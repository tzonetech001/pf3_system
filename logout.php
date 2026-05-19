<?php
session_start();
require_once 'includes/db.php';

if (isLoggedIn()) {
    logAudit($_SESSION['user_id'], $_SESSION['user_type'], 'Logout');
}

session_destroy();
header('Location: index.php');
exit;
?>