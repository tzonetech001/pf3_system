<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $tables = ['admins' => 'admin', 'doctors' => 'doctor', 'police_officers' => 'police'];
    $user = null;
    $role = null;

    foreach ($tables as $table => $r) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password'])) {
            $user = $u;
            $role = $r;
            break;
        }
    }

    if ($user && $role) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $role;
        $_SESSION['user_name'] = isset($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : $user['username'];

        logAudit($user['id'], $role, 'Login');

        switch ($role) {
            case 'admin':
                header('Location: ../admin/dashboard.php');
                break;
            case 'doctor':
                header('Location: ../doctor/dashboard.php');
                break;
            case 'police':
                header('Location: ../police/dashboard.php');
                break;
        }
        exit;
    } else {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ../login.php');
        exit;
    }
}
?>