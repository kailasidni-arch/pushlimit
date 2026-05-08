<?php
session_start();
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') header("Location: admin/index.php");
    elseif ($role === 'trainer') header("Location: trainer/index.php");
    else header("Location: member/index.php");
} else {
    header("Location: auth/login.php");
}
exit;
