<?php
require_once '../includes/auth.php';
requireRole('member', '../auth/login.php');
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member – GymPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- Hamburger Button -->
<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar">
    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="sidebar-brand">
        <h2>GYM<em>PRO</em></h2>
        <p>Member Portal</p>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Menu</div>
        <a href="index.php" class="<?= $current_page==='index'?'active':'' ?>"><span class="icon">🏠</span> Dashboard</a>
        <a href="booking.php" class="<?= $current_page==='booking'?'active':'' ?>"><span class="icon">📅</span> Book Jadwal</a>
        <a href="history.php" class="<?= $current_page==='history'?'active':'' ?>"><span class="icon">📋</span> Riwayat Booking</a>
        <a href="payment_history.php" class="<?= $current_page==='payment_history'?'active':'' ?>"><span class="icon">💳</span> Riwayat Bayar</a>
        <a href="extend.php" class="<?= $current_page==='extend'?'active':'' ?>"><span class="icon">🔄</span> Perpanjang Paket</a>
        <div class="menu-label">Profil</div>
        <a href="ecard.php" class="<?= $current_page==='ecard'?'active':'' ?>"><span class="icon">🪪</span> Kartu Member</a>
        <a href="location.php" class="<?= $current_page==='location'?'active':'' ?>"><span class="icon">📍</span> Lokasi Gym</a>
        <a href="profile.php" class="<?= $current_page==='profile'?'active':'' ?>"><span class="icon">👤</span> Edit Profil</a>
        <div class="menu-label">Akun</div>
        <a href="../auth/logout.php"><span class="icon">🚪</span> Logout</a>
    </nav>
</div>