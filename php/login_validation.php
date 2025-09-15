<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.html');
    exit();
}

require_once __DIR__ . '/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if ($username === '') { $errors['username'] = 'Username is required.'; }
if ($password === '') { $errors['password'] = 'Password is required.'; }


function render_login_form($oldUsername, $errors) {
    $u = htmlspecialchars($oldUsername ?? '', ENT_QUOTES, 'UTF-8');
    $uClass = isset($errors['username']) ? 'invalid' : '';
    $pClass = isset($errors['password']) ? 'invalid' : '';
    $uErr = isset($errors['username']) ? '<div class="error-text">'.htmlspecialchars($errors['username']).'</div>' : '';
    $pErr = isset($errors['password']) ? '<div class="error-text">'.htmlspecialchars($errors['password']).'</div>' : '';
    // TODO: Add HTML output for login form
}

if (!empty($errors)) {
    render_login_form($username, $errors);
    exit();
}

$stmt = $mysqli->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    render_login_form($username, ['username' => 'Server error. Please try again later.']);
    exit();
}
$stmt->bind_param('s', $username);
$stmt->execute();

$user = null;
if (function_exists('mysqli_stmt_get_result')) {
    $result = $stmt->get_result();
    if ($result) { $user = $result->fetch_assoc(); }
} else {
    $stmt->bind_result($id, $uname, $hash, $role);
    if ($stmt->fetch()) {
        $user = ['id'=>$id,'username'=>$uname,'password_hash'=>$hash,'role'=>$role];
    }
}
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    render_login_form($username, ['password' => 'Invalid username or password.']);
    exit();
}

$otp = random_int(100000, 999999);
$_SESSION['otp_user_id'] = $user['id'];
$_SESSION['otp_username'] = $user['username'];
$_SESSION['otp_role'] = $user['role'];
$_SESSION['otp_code'] = (string)$otp;
$_SESSION['otp_expires'] = time() + 300; // 5 minutes

setcookie('rh_username', rawurlencode($user['username']), time()+900, '/');

header('Location: ../view/otp.html');
exit();
?>
