<?php // schedules.php
require_once '../includes/db.php';
require_once 'header.php';
$trainer_id = $_SESSION['user_id'];

$schedules = $pdo->prepare("
    SELECT s.*, COUNT(b.booking_id) AS total_bookings,
           SUM(b.status='confirmed') AS confirmed,
           SUM(b.status='pending') AS pending
    FROM SCHEDULE s
    LEFT JOIN BOOKING b ON s.schedule_id = b.schedule_id
    WHERE s.trainer_id = ?
    GROUP BY s.schedule_id
    ORDER BY s.date DESC
");
$schedules->execute([$trainer_id]);
$schedules = $schedules->fetchAll();
?>

<div class="main-content">
    <div class="topbar"><h1>📅 Jadwal Saya</h1></div>
    <div class="content">
        <div class="card">
            <div class="card-header"><h3>Semua Jadwal</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Waktu</th><th>Total Booking</th><th>Confirmed</th><th>Pending</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📅</div><p>Belum ada jadwal.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($s['date'])) ?></td>
                            <td><?= $s['time'] ?></td>
                            <td><?= $s['total_bookings'] ?></td>
                            <td><span class="status-badge status-confirmed"><?= $s['confirmed'] ?></span></td>
                            <td><span class="status-badge status-pending"><?= $s['pending'] ?></span></td>
                            <td><a href="members.php?schedule_id=<?= $s['schedule_id'] ?>" class="btn btn-edit btn-sm">👥 Lihat Member</a></td>
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
