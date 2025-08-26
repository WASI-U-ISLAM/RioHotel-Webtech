<?php
if ($_SERVER["REQUEST_METHOD"]=="POST")
{
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)){
        echo"<h2 style='text-align:center; color:red;'>All fields are required!<h2>";
    }
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
       echo"<h2 style='text-align:center; color:red;'>Invalid email format!</h2>"; 
    }
    elseif($password!==$confirm_password){
        echo"<h2 style='text-align:center; color:red;'>Passwords do not matched!</h2>";
    }
    else{
        echo"h2 style='text-align:center; color:green;'>Registration Successful!</h2>"; echo"<p style='text-align:center;'>Welcome, $username! You can now <a herf='login.html'>Login</a>.</p>";
    }
    else{
        header("Location:register.html");
        exit();
    }

}
?>