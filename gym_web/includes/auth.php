<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin($redirect = '../auth/login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireRole($role, $redirect = '../auth/login.php') {
    requireLogin($redirect);
    if ($_SESSION['role'] !== $role) {
        header("Location: $redirect");
        exit;
    }
}

function getCurrentUser() {
    return $_SESSION ?? [];
}

function logout() {
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}
?>
