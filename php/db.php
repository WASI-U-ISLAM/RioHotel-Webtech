<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; 
$DB_NAME = 'riohotel';
$DB_PORT = 3306;

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

// Check connection
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'Failed to connect to MySQL: ' . htmlspecialchars($mysqli->connect_error);
    exit;
}


// Set charset
$mysqli->set_charset('utf8mb4');
?>