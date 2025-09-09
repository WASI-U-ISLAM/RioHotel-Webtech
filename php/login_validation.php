<?php
$valid_username = "admin";
$valid_password = "1234";

session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $users = [
        ["username" => "admin", "password" => "1234", "role" => "admin"],
        ["username" => "guest", "password" => "guestpass", "role" => "guest"],
        ["username" => "housekeeper", "password" => "housepass", "role" => "housekeeper"],
        ["username" => "receptionist", "password" => "receppass", "role" => "receptionist"]
    ];
    $found = false;
    foreach ($users as $user) {
        if ($username === $user["username"] && $password === $user["password"]) {
            $found = true;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $user["role"];
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION["otp"] = $otp;
            echo "<h2>Enter the OTP sent to you (for demo: $otp)</h2>";
            echo '<form method="post" action="otp_validation.php">';
            echo '<input type="text" name="otp" placeholder="Enter OTP" required />';
            echo '<input type="submit" value="Verify OTP" />';
            echo '</form>';
            exit();
        }
    }
    if (!$found) {
        header("Location: ../view/logIn.html?error=invalid");
        exit();
    }
}
else {
    echo "Please submit the form.";
}
?>
