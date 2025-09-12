<?php
// Simple database connection (auto create database + users table)
// Basic DB configuration (adjust if you changed XAMPP defaults)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP root password (empty)
$DB_NAME = 'riohotel';
$DB_PORT = 3306; // Change if MySQL runs on another port

// Helper to output a friendly error and stop
function db_fail($msg, $detail = '') {
    http_response_code(500);
    echo '<h3 style="font-family:Arial;">Database Connection Error</h3>';
    echo '<p style="font-family:Arial;">' . htmlspecialchars($msg) . '</p>';
    if ($detail !== '') {
        echo '<pre style="background:#f5f5f5;padding:8px;border:1px solid #ddd;white-space:pre-wrap;">' . htmlspecialchars($detail) . '</pre>';
    }
    echo '<ol style="font-family:Arial;font-size:14px;line-height:1.4;">'
        .'<li>Open XAMPP Control Panel and ensure MySQL is <strong>Running</strong>.</li>'
        .'<li>If port conflict, change MySQL port or stop conflicting service.</li>'
        .'<li>If you changed the port, update $DB_PORT in php/db.php.</li>'
        .'<li>If you set a root password, update $DB_PASS accordingly.</li>'
        .'<li>Restart Apache after changes.</li>'
        .'</ol>';
    exit;
}

// Attempt primary connection
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, '', $DB_PORT);
if ($mysqli->connect_errno) {
    // Fallback: try explicit 127.0.0.1 if localhost failed (IPv6/DNS edge cases)
    $fallback = @new mysqli('127.0.0.1', $DB_USER, $DB_PASS, '', $DB_PORT);
    if ($fallback->connect_errno) {
        db_fail('Could not connect to MySQL server.', $fallback->connect_error);
    }
    $mysqli = $fallback; // use fallback connection
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