<?php
require_once '../includes/auth.php';
requireRole('admin', '../auth/login.php');
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – GymPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=1">
</head>
<body>
    <!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar">
    <!-- 1. Tombol Hamburger di dalam sidebar -->
    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- 2. Brand/Judul di bawah tombol -->
    <div class="sidebar-brand">
        <h2>GYM<em>PRO</em></h2>
        <p>Admin Panel</p>
    </div>

    <nav class="sidebar-menu">
        <!-- Menu kamu selanjutnya... -->
        <a href="index.php" class="<?= $current_page==='index'?'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
        <div class="menu-label">Management</div>
        <a href="members.php" class="<?= $current_page==='members'?'active':'' ?>"><span class="icon">👥</span> Members</a>
        <a href="trainers.php" class="<?= $current_page==='trainers'?'active':'' ?>"><span class="icon">🏋️</span> Trainers</a>
        <a href="packages.php" class="<?= $current_page==='packages'?'active':'' ?>"><span class="icon">📦</span> Packages</a>
        <a href="schedules.php" class="<?= $current_page==='schedules'?'active':'' ?>"><span class="icon">📅</span> Schedules</a>
        <a href="bookings.php" class="<?= $current_page==='bookings'?'active':'' ?>"><span class="icon">📋</span> Bookings</a>
        <a href="payments.php" class="<?= $current_page==='payments'?'active':'' ?>"><span class="icon">💳</span> Payments</a>
        <div class="menu-label">Reports & Settings</div>
        <a href="reports.php" class="<?= $current_page==='reports'?'active':'' ?>"><span class="icon">📊</span> Reports</a>
        <a href="settings.php" class="<?= $current_page==='settings'?'active':'' ?>"><span class="icon">⚙️</span> Settings</a>
        <div class="menu-label">Akun</div>
        <a href="../auth/logout.php"><span class="icon">🚪</span> Logout</a>
    </nav>
</div>