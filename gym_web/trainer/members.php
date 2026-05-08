<?php
require_once '../includes/db.php';
require_once 'header.php';
$trainer_id = $_SESSION['user_id'];
$message = '';

// Update booking status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE BOOKING SET status=? WHERE booking_id=?")->execute([$_POST['status'], $_POST['booking_id']]);
    $message = '<div class="alert alert-success">✅ Status booking diperbarui!</div>';
}

$schedule_id = $_GET['schedule_id'] ?? null;
$where = $schedule_id ? "AND s.schedule_id = $schedule_id" : "";

$bookings = $pdo->prepare("
    SELECT b.booking_id, m.name AS member_name, m.phone, s.date, s.time, b.status, s.schedule_id
    FROM BOOKING b
    JOIN MEMBER m ON b.member_id = m.member_id
    JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    WHERE s.trainer_id = ? $where
    ORDER BY s.date DESC, m.name ASC
");
$bookings->execute([$trainer_id]);
$bookings = $bookings->fetchAll();

// Get schedules for filter
$schedulesList = $pdo->prepare("SELECT * FROM SCHEDULE WHERE trainer_id=? ORDER BY date DESC");
$schedulesList->execute([$trainer_id]);
$schedulesList = $schedulesList->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>👥 Member Saya</h1>
        <?php if ($schedule_id): ?>
        <div class="topbar-right"><a href="members.php" class="btn btn-secondary btn-sm">Lihat Semua</a></div>
        <?php endif; ?>
    </div>
    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header">
                <h3>Daftar Member & Status Booking</h3>
                <form method="GET" style="display:flex; gap:8px;">
                    <select name="schedule_id" onchange="this.form.submit()" style="padding:8px 12px; border:1.5px solid #e0e3e8; border-radius:8px; font-size:13px;">
                        <option value="">Semua Jadwal</option>
                        <?php foreach ($schedulesList as $s): ?>
                        <option value="<?= $s['schedule_id'] ?>" <?= $schedule_id == $s['schedule_id'] ? 'selected' : '' ?>><?= $s['date'] ?> <?= $s['time'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>Member</th><th>Telepon</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Update Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👥</div><p>Belum ada member.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['member_name']) ?></td>
                            <td><?= htmlspecialchars($b['phone']) ?></td>
                            <td><?= date('d M Y', strtotime($b['date'])) ?></td>
                            <td><?= $b['time'] ?></td>
                            <td><span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td>
                                <form method="POST" style="display:flex; gap:6px;">
                                    <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                    <select name="status" style="padding:5px 8px; border:1.5px solid #e0e3e8; border-radius:6px; font-size:12px;">
                                        <option value="pending" <?= $b['status']==='pending'?'selected':'' ?>>Pending</option>
                                        <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-edit btn-sm">Update</button>
                                </form>
                            </td>
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
