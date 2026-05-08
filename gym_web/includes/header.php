<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymPro Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>css/style.css">
</head>
<body>

<!-- Hamburger Button -->
<button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()">
  <span></span>
  <span></span>
  <span></span>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2>GYM<em>PRO</em></h2>
        <p>Management System</p>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Main</div>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php"
           class="<?= $current_page === 'index' ? 'active' : '' ?>">
            <span class="icon">🏠</span> Dashboard
        </a>
        <div class="menu-label">Management</div>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/members.php"
           class="<?= $current_page === 'members' ? 'active' : '' ?>">
            <span class="icon">👥</span> Members
        </a>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/trainers.php"
           class="<?= $current_page === 'trainers' ? 'active' : '' ?>">
            <span class="icon">🏋️</span> Trainers
        </a>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/packages.php"
           class="<?= $current_page === 'packages' ? 'active' : '' ?>">
            <span class="icon">📦</span> Packages
        </a>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/schedules.php"
           class="<?= $current_page === 'schedules' ? 'active' : '' ?>">
            <span class="icon">📅</span> Schedules
        </a>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/bookings.php"
           class="<?= $current_page === 'bookings' ? 'active' : '' ?>">
            <span class="icon">📋</span> Bookings
        </a>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/payments.php"
           class="<?= $current_page === 'payments' ? 'active' : '' ?>">
            <span class="icon">💳</span> Payments
        </a>
        <div class="menu-label">Reports</div>
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/reports.php"
           class="<?= $current_page === 'reports' ? 'active' : '' ?>">
            <span class="icon">📊</span> Reports
        </a>
    </nav>
</div>

