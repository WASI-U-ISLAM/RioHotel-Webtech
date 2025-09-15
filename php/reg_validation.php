<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/register.html');
    exit();
}

require_once __DIR__ . '/db.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

$allowed_roles = ['admin','guest','housekeeper','receptionist'];

function render_register_form($old, $errors) {
    $u = htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8');
    $e = htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $r = $old['role'] ?? '';
    $cls = function($k) use ($errors){ return isset($errors[$k]) ? 'invalid' : ''; };
    $err = function($k) use ($errors){ return isset($errors[$k]) ? '<div class="error-text">'.htmlspecialchars($errors[$k]).'</div>' : ''; };
    $sel = function($v,$r){ return $r===$v ? 'selected' : ''; };
    echo '<!DOCTYPE html><html><head><title>Register</title><link rel="stylesheet" href="../css/style.css"><style>.invalid{border:1.5px solid #e74c3c!important;background:#fff5f5!important}.error-text{color:#e74c3c;font-size:.95em;margin-top:6px}</style></head><body><div class="login-container"><h2>Register</h2><form action="../php/reg_validation.php" method="post"><div class="input-group"><label for="username">Username:</label><input type="text" id="username" name="username" value="'.$u.'" class="'.$cls('username').'">'.$err('username').'</div><div class="input-group"><label for="email">Email:</label><input type="email" id="email" name="email" value="'.$e.'" class="'.$cls('email').'">'.$err('email').'</div><div class="input-group"><label for="password">Password:</label><input type="password" id="password" name="password" class="'.$cls('password').'">'.$err('password').'</div><div class="input-group"><label for="confirm_password">Confirm Password:</label><input type="password" id="confirm_password" name="confirm_password" class="'.$cls('confirm_password').'">'.$err('confirm_password').'</div><div class="input-group"><label for="role">Role:</label><select id="role" name="role" class="'.$cls('role').'"><option value="">Select Role</option><option value="guest" ' . $sel('guest',$r) . '>Guest</option><option value="admin" ' . $sel('admin',$r) . '>Admin</option><option value="housekeeper" ' . $sel('housekeeper',$r) . '>Housekeeper</option><option value="receptionist" ' . $sel('receptionist',$r) . '>Receptionist</option></select>'.$err('role').'</div><div class="input-group"><input type="submit" value="Register"></div></form></div></body></html>';
}

$errors = [];
if ($username === '') { $errors['username'] = 'Username is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
if ($password === '') { $errors['password'] = 'Password is required.'; }
if ($confirm_password === '') { $errors['confirm_password'] = 'Confirm your password.'; }
if ($password !== '' && $confirm_password !== '' && $password !== $confirm_password) { $errors['confirm_password'] = 'Passwords do not match.'; }
if (!in_array($role, $allowed_roles, true)) { $errors['role'] = 'Please select a valid role.'; }

$old = ['username'=>$username,'email'=>$email,'role'=>$role];
if (!empty($errors)) { render_register_form($old, $errors); exit(); }

$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
if (!$stmt) { render_register_form($old, ['username'=>'Server error. Please try again.']); exit(); }
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) { $stmt->close(); render_register_form($old, ['username'=>'Username or email already exists.']); exit(); }
$stmt->close();

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $mysqli->prepare('INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,?)');
if (!$stmt) { render_register_form($old, ['username'=>'Server error. Please try again.']); exit(); }
$stmt->bind_param('ssss', $username, $email, $hash, $role);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: ../view/login.html');
    exit();
}
$stmt->close();
render_register_form($old, ['username'=>'Server error. Please try again.']);
exit();
?>
