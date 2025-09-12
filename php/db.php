<?php
// Simple database connection (auto create database + users table)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP root password (empty)
$DB_NAME = 'riohotel';

// Connect (create DB if missing)
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'Database server connection failed: ' . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

// Create database if missing
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    http_response_code(500);
    echo 'Failed creating database: ' . htmlspecialchars($mysqli->error);
    exit;
}

// Select database
if (!$mysqli->select_db($DB_NAME)) {
    http_response_code(500);
    echo 'Failed selecting database.';
    exit;
}

// Make sure users table exists
$createUsers = "CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','guest','housekeeper','receptionist') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if (!$mysqli->query($createUsers)) {
    http_response_code(500);
    echo 'Failed ensuring users table: ' . htmlspecialchars($mysqli->error);
    exit;
}

// Seed admin user if not present
$res = $mysqli->query("SELECT id FROM users WHERE username='admin' LIMIT 1");
if ($res && $res->num_rows === 0) {
    $seedHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare('INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?,?)');
    if ($stmt) {
        $email = 'admin@example.com';
        $role = 'admin';
        $stmt->bind_param('ssss', $adminUser, $email, $seedHash, $role);
        $adminUser = 'admin';
        $stmt->execute();
        $stmt->close();
    }
}
?>