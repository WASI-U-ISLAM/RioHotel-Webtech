<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['otp']) || !isset($_POST['otp'])) {
        header('Location: ../view/logIn.html?error=otp');
        exit();
    }
    $entered = trim($_POST['otp']);
    if ($entered === strval($_SESSION['otp'])) {
        // OTP correct – use role to redirect
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
        // Invalidate OTP after success
        unset($_SESSION['otp']);
        switch ($role) {
            case 'admin':
                header('Location: ../view/admin_dashboard.html');
                break;
            case 'guest':
                header('Location: ../view/guest_dashboard.html');
                break;
            case 'housekeeper':
                header('Location: ../view/housekeeper_dashboard.html');
                break;
            case 'receptionist':
                header('Location: ../view/receptionist_dashboard.html');
                break;
            default:
                // Unknown role – force logout / re-login
                session_destroy();
                header('Location: ../view/logIn.html?error=invalid');
        }
        exit();
    } else {
        // Wrong OTP
        header('Location: ../view/logIn.html?error=otp');
        exit();
    }
} else {
    header('Location: ../view/logIn.html');
    exit();
}
?>