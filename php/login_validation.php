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

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

setcookie('rh_username', rawurlencode($user['username']), time()+3600, '/');

switch ($user['role']) {
    case 'admin':
        header('Location: ../view/admin_dashboard.html');
        break;
    case 'housekeeper':
        header('Location: ../view/housekeeping.html');
        break;
    case 'receptionist':
        header('Location: ../view/receptionist.html');
        break;
    default: // guest
        header('Location: ../view/guest.html');
        break;
}
exit();
?>
