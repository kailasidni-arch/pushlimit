<?php
require_once '../includes/auth.php';
requireRole('trainer', '../auth/login.php');
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer – GymPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-brand h2 em { color: #4ADE80; }
        .sidebar-menu a.active { background: linear-gradient(90deg,rgba(74,222,128,0.12) 0%,transparent 100%); border-left-color: #4ADE80; }
        .sidebar-menu a:hover { border-left-color: rgba(74,222,128,0.5); }
    </style>
</head>
<body>

<!-- Hamburger Button -->
<!-- Overlay -->
<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="sidebar-brand">
        <h2>GYM<em>PRO</em></h2>
        <p>Trainer Portal</p>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Menu</div>
        <a href="index.php" class="<?= $current_page==='index'?'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
        <a href="schedules.php" class="<?= $current_page==='schedules'?'active':'' ?>"><span class="icon">📅</span> Jadwal Saya</a>
        <a href="members.php" class="<?= $current_page==='members'?'active':'' ?>"><span class="icon">👥</span> Member Saya</a>
        <div class="menu-label">Akun</div>
        <a href="profile.php" class="<?= $current_page==='profile'?'active':'' ?>"><span class="icon">👤</span> Profil</a>
        <a href="../auth/logout.php"><span class="icon">🚪</span> Logout</a>
    </nav>
</div>