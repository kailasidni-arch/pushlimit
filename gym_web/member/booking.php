<?php // booking.php
require_once '../includes/db.php';
require_once 'header.php';

$member_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    // Check duplicate
    $check = $pdo->prepare("SELECT booking_id FROM BOOKING WHERE member_id = ? AND schedule_id = ?");
    $check->execute([$member_id, $schedule_id]);
    if ($check->fetch()) {
        $message = '<div class="alert alert-error">❌ Kamu sudah booking jadwal ini!</div>';
    } else {
        $pdo->prepare("INSERT INTO BOOKING (member_id, schedule_id, status) VALUES (?, ?, 'pending')")->execute([$member_id, $schedule_id]);
        $message = '<div class="alert alert-success">✅ Booking berhasil! Menunggu konfirmasi trainer.</div>';
    }
}

$schedules = $pdo->query("
    SELECT s.*, t.name AS trainer_name, t.specialization,
           COUNT(b.booking_id) AS booked_count
    FROM SCHEDULE s
    JOIN TRAINER t ON s.trainer_id = t.trainer_id
    LEFT JOIN BOOKING b ON s.schedule_id = b.schedule_id
    WHERE s.date >= CURDATE()
    GROUP BY s.schedule_id
    ORDER BY s.date ASC, s.time ASC
")->fetchAll();

// Get already booked schedules by this member
$myBookings = $pdo->prepare("SELECT schedule_id FROM BOOKING WHERE member_id = ?");
$myBookings->execute([$member_id]);
$myBookedIds = array_column($myBookings->fetchAll(), 'schedule_id');
?>

<div class="main-content">
    <div class="topbar"><h1>📅 Book Jadwal Latihan</h1></div>
    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header"><h3>Jadwal Tersedia</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Waktu</th><th>Trainer</th><th>Spesialisasi</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($s['date'])) ?></td>
                            <td><?= $s['time'] ?></td>
                            <td><?= htmlspecialchars($s['trainer_name']) ?></td>
                            <td><?= htmlspecialchars($s['specialization']) ?></td>
                            <td>
                                <?php if (in_array($s['schedule_id'], $myBookedIds)): ?>
                                <span class="status-badge status-confirmed">✅ Sudah Dibooking</span>
                                <?php else: ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="schedule_id" value="<?= $s['schedule_id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Book</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($schedules)): ?>
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">📅</div><p>Belum ada jadwal tersedia.</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
