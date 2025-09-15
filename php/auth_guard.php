<?php
session_start();
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: ../view/login.html?error=auth');
    exit();
}

function require_role(array $roles){
    if (!in_array($_SESSION['role'], $roles, true)) {
        header('Location: ../view/login.html?error=denied');
        exit() ;
    }

}
?>