<?php
require_once '../includes/db.php';
require_once 'header.php';
$trainer_id = $_SESSION['user_id'];

$totalSchedules  = $pdo->prepare("SELECT COUNT(*) FROM SCHEDULE WHERE trainer_id=?"); $totalSchedules->execute([$trainer_id]); $totalSchedules=$totalSchedules->fetchColumn();
$totalBookings   = $pdo->prepare("SELECT COUNT(*) FROM BOOKING b JOIN SCHEDULE s ON b.schedule_id=s.schedule_id WHERE s.trainer_id=?"); $totalBookings->execute([$trainer_id]); $totalBookings=$totalBookings->fetchColumn();
$pendingBookings = $pdo->prepare("SELECT COUNT(*) FROM BOOKING b JOIN SCHEDULE s ON b.schedule_id=s.schedule_id WHERE s.trainer_id=? AND b.status='pending'"); $pendingBookings->execute([$trainer_id]); $pendingBookings=$pendingBookings->fetchColumn();

$upcomingSchedules = $pdo->prepare("SELECT s.*,COUNT(b.booking_id) AS total_bookings FROM SCHEDULE s LEFT JOIN BOOKING b ON s.schedule_id=b.schedule_id WHERE s.trainer_id=? AND s.date>=CURDATE() GROUP BY s.schedule_id ORDER BY s.date ASC LIMIT 5");
$upcomingSchedules->execute([$trainer_id]);
$upcomingSchedules = $upcomingSchedules->fetchAll();
?>

<div class="main-content">
    <div style="background:linear-gradient(135deg,#161616 0%,#091a10 100%);border-bottom:1px solid rgba(255,255,255,0.09);padding:28px 28px 26px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(74,222,128,0.1) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,#4ADE80,transparent);opacity:0.4;"></div>
        <div style="font-size:10.5px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:rgba(74,222,128,0.9);margin-bottom:6px;">TRAINER PORTAL</div>
        <h1 style="font-family:'Bebas Neue',sans-serif;font-size:38px;letter-spacing:2px;line-height:1;color:#fff;">HALO, <span style="color:#4ADE80;"><?= strtoupper(htmlspecialchars(explode(' ',$_SESSION['name'])[0])) ?>!</span></h1>
        <div style="font-size:13px;color:rgba(255,255,255,0.4);margin-top:6px;"><?= date('l, d F Y') ?></div>
    </div>

    <div class="content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📅</div>
                <div class="stat-info"><h3><?= $totalSchedules ?></h3><p>Total Jadwal</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">👥</div>
                <div class="stat-info"><h3><?= $totalBookings ?></h3><p>Total Booking</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">⏳</div>
                <div class="stat-info"><h3><?= $pendingBookings ?></h3><p>Pending</p></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>📅 Jadwal Mendatang</h3>
                <a href="schedules.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Tanggal</th><th>Waktu</th><th>Total Booking</th></tr></thead>
                    <tbody>
                        <?php if(empty($upcomingSchedules)): ?>
                        <tr><td colspan="3"><div class="empty-state"><div class="empty-icon">📅</div><p>Belum ada jadwal mendatang</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($upcomingSchedules as $s): ?>
                        <tr>
                            <td style="font-weight:700;"><?= date('d M Y', strtotime($s['date'])) ?></td>
                            <td style="color:rgba(255,255,255,0.6);"><?= $s['time'] ?></td>
                            <td><span style="background:rgba(74,222,128,0.12);color:#4ADE80;border:1px solid rgba(74,222,128,0.3);padding:3px 10px;border-radius:4px;font-size:12px;font-weight:700;"><?= $s['total_bookings'] ?> member</span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
