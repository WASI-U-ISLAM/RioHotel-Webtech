<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['preview']) && $_GET['preview'] === '1') {
                if (isset($_SESSION['otp_code'], $_SESSION['otp_expires']) && time() <= $_SESSION['otp_expires']) {
                        header('Content-Type: text/plain');
                        echo $_SESSION['otp_code'];
                        exit();
                }
                http_response_code(204);
                exit();
        }
        if (isset($_GET['status']) && $_GET['status'] === '1') {
                header('Content-Type: text/html; charset=UTF-8');
                if (!empty($_SESSION['otp_error'])) {
                        echo '<div class="popup-bar" style="background:#e74c3c;color:#fff;padding:10px;border-radius:6px;">' . htmlspecialchars($_SESSION['otp_error']) . '</div>';
                        unset($_SESSION['otp_error']);
                }
                exit();
        }
        header('Location: ../view/otp.html');
        exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../view/login.html');
        exit();
}

$input = trim($_POST['otp'] ?? '');
if ($input === '' || !preg_match('/^\d{6}$/', $input)) {
    $_SESSION['otp_error'] = 'Invalid code. Please try again.';
    header('Location: ../view/otp.html');
        exit();
}
if (!isset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_user_id'])) {
                header('Location: ../view/login.html?error=auth');
        exit();
}
if (time() > $_SESSION['otp_expires']) {
    unset($_SESSION['otp_code']);
    $_SESSION['otp_error'] = 'Code expired. Please log in again.';
    header('Location: ../view/otp.html');
        exit();
}
if ($input !== $_SESSION['otp_code']) {
    $_SESSION['otp_error'] = 'Invalid code. Please try again.';
    header('Location: ../view/otp.html');
        exit();
}

$_SESSION['user_id'] = $_SESSION['otp_user_id'];
$_SESSION['username'] = $_SESSION['otp_username'];
$_SESSION['role'] = $_SESSION['otp_role'];
setcookie('rh_username', rawurlencode($_SESSION['username']), time()+3600, '/');
unset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_user_id'], $_SESSION['otp_username'], $_SESSION['otp_role']);
switch($_SESSION['role']){
        case 'admin': header('Location: ../view/admin_dashboard.html'); break;
        case 'housekeeper': header('Location: ../view/housekeeping.html'); break;
        case 'receptionist': header('Location: ../view/receptionist.html'); break;
        default: header('Location: ../view/guest.html'); break;
}
exit();
?>