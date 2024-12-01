<?php
session_start();

function authenticate_user() {
    if (!isset($_SESSION['email'])) {
        header("Location: sign-up.php");
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function is_user(){
    return isset($_SESSION['role']) && $_SESSION['role'] == 'user';
}

function require_admin_access() {
    authenticate_user();
    if (!is_admin()) {
        header("Location: sign-up.php");
        exit();
    }
}

// Check if the current page requires user access
function require_user_access() {
    authenticate_user();
    if (!is_user()) {
        header("Location: sign-up.php");
        exit();
    }
}

function require_customer_access(){
    authenticate_user();
}

?>