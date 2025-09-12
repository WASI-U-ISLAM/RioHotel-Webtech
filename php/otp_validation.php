<?php
session_start();
// If GET with preview=1 return current OTP (demo only)
if($_SERVER['REQUEST_METHOD']==='GET'){
    if(isset($_GET['preview']) && $_GET['preview']==='1' && isset($_SESSION['otp_code'], $_SESSION['otp_expires']) && time() <= $_SESSION['otp_expires']){
        header('Content-Type: text/plain');
        echo $_SESSION['otp_code'];
        exit();
    }
    http_response_code(404);
    exit();
}
// Otherwise must be POST to validate
if($_SERVER['REQUEST_METHOD']!=='POST'){
    header('Location: ../view/login.html');
    exit();
}
$input = trim($_POST['otp'] ?? '');
if($input===''){header('Location: ../view/otp.html?error=invalid');exit();}
if(!isset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_user_id'])){
    header('Location: ../view/login.html?error=auth');
    exit();
}
if(time() > $_SESSION['otp_expires']){
    unset($_SESSION['otp_code']);
    header('Location: ../view/otp.html?error=expired');
    exit();
}
if($input !== $_SESSION['otp_code']){
    header('Location: ../view/otp.html?error=invalid');
    exit();
}
// Promote to full session
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