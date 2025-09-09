<?php
$valid_username = "admin";
$valid_password = "1234";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username === $valid_username && $password === $valid_password) {
        echo "Login successful!";
    } else {
        echo "Invalid username or password.";
    }
}
else {
    echo "Please submit the form.";
}
?>
