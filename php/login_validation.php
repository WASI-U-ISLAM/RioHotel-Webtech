<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.html');
    exit();
}

require_once __DIR__ . '/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../view/login.html?error=invalid');
    exit();
}

$stmt = $mysqli->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    header('Location: ../view/login.html?error=server');
    exit();
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    header('Location: ../view/login.html?error=invalid');
    exit();
}

// Create OTP (6 digits)
$otp = random_int(100000, 999999);
$_SESSION['otp_user_id'] = $user['id'];
$_SESSION['otp_username'] = $user['username'];
$_SESSION['otp_role'] = $user['role'];
$_SESSION['otp_code'] = (string)$otp;
$_SESSION['otp_expires'] = time() + 300; // 5 minutes


setcookie('rh_username', rawurlencode($user['username']), time()+900, '/');

header('Location: ../view/otp.html?show=1');
exit();
?>
