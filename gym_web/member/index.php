<?php
require_once '../includes/db.php';
require_once 'header.php';

$member_id = $_SESSION['user_id'];

$member = $pdo->prepare("SELECT m.*, p.package_name, p.duration, p.price FROM MEMBER m LEFT JOIN PACKAGE p ON m.package_id = p.package_id WHERE m.member_id = ?");
$member->execute([$member_id]);
$member = $member->fetch();

$totalBookings = $pdo->prepare("SELECT COUNT(*) FROM BOOKING WHERE member_id = ?");
$totalBookings->execute([$member_id]);
$totalBookings = $totalBookings->fetchColumn();

$confirmedBookings = $pdo->prepare("SELECT COUNT(*) FROM BOOKING WHERE member_id = ? AND status = 'confirmed'");
$confirmedBookings->execute([$member_id]);
$confirmedBookings = $confirmedBookings->fetchColumn();

$totalPaid = $pdo->prepare("SELECT SUM(amount) FROM PAYMENT WHERE member_id = ?");
$totalPaid->execute([$member_id]);
$totalPaid = $totalPaid->fetchColumn() ?? 0;

$upcomingBookings = $pdo->prepare("
    SELECT b.*, s.date, s.time, t.name AS trainer_name, t.specialization
    FROM BOOKING b
    JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    JOIN TRAINER t ON s.trainer_id = t.trainer_id
    WHERE b.member_id = ? AND s.date >= CURDATE()
    ORDER BY s.date ASC LIMIT 3
");
$upcomingBookings->execute([$member_id]);
$upcomingBookings = $upcomingBookings->fetchAll();
?>

<div class="main-content">
    <!-- Hero Topbar -->
    <div style="
        background: linear-gradient(135deg, #161616 0%, #1a0f0a 100%);
        border-bottom: 1px solid rgba(255,255,255,0.09);
        padding: 28px 28px 26px;
        position: relative; overflow: hidden;
    ">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(232,56,13,0.12) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--accent),transparent);opacity:0.4;"></div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:rgba(232,56,13,0.9);margin-bottom:6px;">SELAMAT DATANG KEMBALI</div>
                <h1 style="font-family:'Bebas Neue',sans-serif;font-size:38px;letter-spacing:2px;line-height:1;color:#fff;">
                    <?= strtoupper(htmlspecialchars(explode(' ', $member['name'])[0])) ?> <span style="color:var(--accent);">!</span>
                </h1>
                <div style="font-size:13.5px;color:rgba(255,255,255,0.5);margin-top:6px;font-weight:500;">
                    Paket aktif: <strong style="color:rgba(255,255,255,0.85);"><?= $member['package_name'] ?? 'Belum ada' ?></strong>
                    <?php if($member['package_expiry']): ?>
                    &nbsp;·&nbsp; Expired: <strong style="color:rgba(255,255,255,0.85);"><?= date('d M Y', strtotime($member['package_expiry'])) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.09);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.6);">
                    📅 <?= date('d M Y') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📦</div>
                <div class="stat-info">
                    <h3><?= $member['package_name'] ?? '-' ?></h3>
                    <p>Paket Aktif</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📋</div>
                <div class="stat-info">
                    <h3><?= $totalBookings ?></h3>
                    <p>Total Booking</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info">
                    <h3><?= $confirmedBookings ?></h3>
                    <p>Booking Confirmed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">💳</div>
                <div class="stat-info">
                    <h3>Rp <?= number_format($totalPaid, 0, ',', '.') ?></h3>
                    <p>Total Pembayaran</p>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <!-- Upcoming Bookings -->
            <div class="card">
                <div class="card-header">
                    <h3>📅 Jadwal Mendatang</h3>
                    <a href="booking.php" class="btn btn-primary btn-sm">+ Book Sekarang</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingBookings)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <p>Belum ada jadwal mendatang</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($upcomingBookings as $b): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#fff;"><?= htmlspecialchars($b['trainer_name']) ?></div>
                            <div style="font-size:12px;color:rgba(255,255,255,0.4);margin-top:2px;"><?= $b['specialization'] ?> · <?= $b['date'] ?> <?= $b['time'] ?></div>
                        </div>
                        <span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header"><h3>⚡ Akses Cepat</h3></div>
                <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <?php
                    $links = [
                        ['href'=>'ecard.php','icon'=>'🪪','label'=>'Kartu Member','color'=>'rgba(59,130,246,0.12)','border'=>'rgba(59,130,246,0.25)'],
                        ['href'=>'location.php','icon'=>'📍','label'=>'Lokasi Gym','color'=>'rgba(34,197,94,0.10)','border'=>'rgba(34,197,94,0.25)'],
                        ['href'=>'booking.php','icon'=>'📅','label'=>'Book Jadwal','color'=>'rgba(232,56,13,0.12)','border'=>'rgba(232,56,13,0.3)'],
                        ['href'=>'profile.php','icon'=>'👤','label'=>'Edit Profil','color'=>'rgba(168,85,247,0.10)','border'=>'rgba(168,85,247,0.25)'],
                    ];
                    foreach($links as $l): ?>
                    <a href="<?= $l['href'] ?>" style="text-decoration:none;">
                        <div style="background:<?= $l['color'] ?>;border:1px solid <?= $l['border'] ?>;border-radius:10px;padding:20px;text-align:center;transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='';this.style.boxShadow='';">
                            <div style="font-size:30px;margin-bottom:8px;"><?= $l['icon'] ?></div>
                            <div style="font-size:12.5px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:0.5px;"><?= $l['label'] ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
