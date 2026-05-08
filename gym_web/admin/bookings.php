<?php
require_once '../includes/db.php';
require_once 'header.php';

$message = '';
$edit_data = null;
$members = $pdo->query("SELECT * FROM MEMBER ORDER BY name")->fetchAll();
$schedules = $pdo->query("SELECT s.*, t.name AS trainer_name FROM SCHEDULE s JOIN TRAINER t ON s.trainer_id = t.trainer_id ORDER BY s.date DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO BOOKING (member_id, schedule_id, status) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['member_id'], $_POST['schedule_id'], $_POST['status']]);
        $message = '<div class="alert alert-success">✅ Booking added successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE BOOKING SET member_id=?, schedule_id=?, status=? WHERE booking_id=?");
        $stmt->execute([$_POST['member_id'], $_POST['schedule_id'], $_POST['status'], $_POST['booking_id']]);
        $message = '<div class="alert alert-success">✅ Booking updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM BOOKING WHERE booking_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Booking deleted successfully!</div>';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM BOOKING WHERE booking_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$bookings = $pdo->query("
    SELECT b.booking_id, m.name AS member_name, s.date, s.time, t.name AS trainer_name, b.status
    FROM BOOKING b
    INNER JOIN MEMBER m ON b.member_id = m.member_id
    INNER JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    INNER JOIN TRAINER t ON s.trainer_id = t.trainer_id
    ORDER BY b.booking_id DESC
")->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>📋 Bookings</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Booking</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header"><h3>Booking List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📋</div><p>No bookings found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?= $b['booking_id'] ?></td>
                            <td><?= htmlspecialchars($b['member_name']) ?></td>
                            <td><?= $b['date'] ?></td>
                            <td><?= $b['time'] ?></td>
                            <td><?= htmlspecialchars($b['trainer_name']) ?></td>
                            <td><span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $b['booking_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $b['booking_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this booking?')">🗑️ Delete</a>
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

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Booking</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Member</label>
                    <select name="member_id" required>
                        <option value="">-- Select Member --</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['member_id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Schedule</label>
                    <select name="schedule_id" required>
                        <option value="">-- Select Schedule --</option>
                        <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['schedule_id'] ?>"><?= $s['date'] ?> <?= $s['time'] ?> - <?= htmlspecialchars($s['trainer_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Booking</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<?php if ($edit_data): ?>
<div class="modal-overlay show" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Booking</h3>
            <a href="bookings.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="booking_id" value="<?= $edit_data['booking_id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Member</label>
                    <select name="member_id" required>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['member_id'] ?>" <?= $m['member_id'] == $edit_data['member_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Schedule</label>
                    <select name="schedule_id" required>
                        <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['schedule_id'] ?>" <?= $s['schedule_id'] == $edit_data['schedule_id'] ? 'selected' : '' ?>><?= $s['date'] ?> <?= $s['time'] ?> - <?= htmlspecialchars($s['trainer_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="pending" <?= $edit_data['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $edit_data['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $edit_data['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Booking</button>
                <a href="bookings.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
</script>

<?php require_once '../includes/footer.php'; ?>
