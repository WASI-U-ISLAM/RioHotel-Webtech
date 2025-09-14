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

// Basic validation
if ($username === '' || $email === '' || $password === '' || $confirm_password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password !== $confirm_password || !in_array($role, $allowed_roles, true)) {
    header('Location: ../view/register.html?error=invalid');
    exit();
}

// Check uniqueness
$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
if (!$stmt) {
    header('Location: ../view/register.html?error=server');
    exit();
}
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: ../view/register.html?error=exists');
    exit();
}
$stmt->close();

// Hash password and insert
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $mysqli->prepare('INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,?)');
if (!$stmt) {
    header('Location: ../view/register.html?error=server');
    exit();
}
$stmt->bind_param('ssss', $username, $email, $hash, $role);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: ../view/login.html?registered=1');
    exit();
}
$stmt->close();
header('Location: ../view/register.html?error=server');
exit();
?>