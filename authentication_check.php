<?php
session_start();

function authenticate_user() {
    if (!isset($_SESSION['email'])) {
        header("Location: sign-up.php");
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['role_txt']) && $_SESSION['role_txt'] == 'admin';
}

function is_artist(){
    return isset($_SESSION['role_txt']) && $_SESSION['role_txt'] == 'artist';
}

function is_customer(){
    return isset($_SESSION['role_txt']) && $_SESSION['role_txt'] == 'customer';
}

function require_admin_access() {
    authenticate_user();
    if (!is_admin()) {
        header("Location: sign-up.php");
        exit();
    }
}

function require_artist_access() {
    authenticate_user();
    if (!is_artist() && !is_admin()) {
        header("Location: sign-up.php");
        exit();
    }
}

function require_customer_access(){
    authenticate_user();
    if (!is_customer()) {
        header("Location: sign-up.php");
        exit();
    }
}

?>