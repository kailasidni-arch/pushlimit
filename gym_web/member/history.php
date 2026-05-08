<?php // history.php
require_once '../includes/db.php';
require_once 'header.php';
$member_id = $_SESSION['user_id'];

// Cancel booking
if (isset($_GET['cancel'])) {
    $pdo->prepare("UPDATE BOOKING SET status='cancelled' WHERE booking_id=? AND member_id=?")->execute([$_GET['cancel'], $member_id]);
    header("Location: history.php?msg=cancelled");
    exit;
}

$bookings = $pdo->prepare("
    SELECT b.*, s.date, s.time, t.name AS trainer_name, t.specialization
    FROM BOOKING b
    JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    JOIN TRAINER t ON s.trainer_id = t.trainer_id
    WHERE b.member_id = ?
    ORDER BY s.date DESC
");
$bookings->execute([$member_id]);
$bookings = $bookings->fetchAll();
?>

<div class="main-content">
    <div class="topbar"><h1>📋 Riwayat Booking</h1></div>
    <div class="content">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
        <div class="alert alert-success">✅ Booking berhasil dibatalkan.</div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h3>Semua Booking</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr><th>#</th><th>Tanggal</th><th>Waktu</th><th>Trainer</th><th>Spesialisasi</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📋</div><p>Belum ada booking.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= $b['booking_id'] ?></td>
                            <td><?= date('d M Y', strtotime($b['date'])) ?></td>
                            <td><?= $b['time'] ?></td>
                            <td><?= htmlspecialchars($b['trainer_name']) ?></td>
                            <td><?= htmlspecialchars($b['specialization']) ?></td>
                            <td><span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td>
                                <?php if ($b['status'] === 'pending' && $b['date'] >= date('Y-m-d')): ?>
                                <a href="?cancel=<?= $b['booking_id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Batalkan booking ini?')">Batal</a>
                                <?php else: ?>
                                <span style="color:#ccc; font-size:12px;">-</span>
                                <?php endif; ?>
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
