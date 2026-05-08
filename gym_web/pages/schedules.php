<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$message = '';
$edit_data = null;
$trainers = $pdo->query("SELECT * FROM TRAINER ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO SCHEDULE (date, time, trainer_id) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['date'], $_POST['time'], $_POST['trainer_id']]);
        $message = '<div class="alert alert-success">✅ Schedule added successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE SCHEDULE SET date=?, time=?, trainer_id=? WHERE schedule_id=?");
        $stmt->execute([$_POST['date'], $_POST['time'], $_POST['trainer_id'], $_POST['schedule_id']]);
        $message = '<div class="alert alert-success">✅ Schedule updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM SCHEDULE WHERE schedule_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Schedule deleted successfully!</div>';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM SCHEDULE WHERE schedule_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$schedules = $pdo->query("
    SELECT s.*, t.name AS trainer_name, t.specialization
    FROM SCHEDULE s
    INNER JOIN TRAINER t ON s.trainer_id = t.trainer_id
    ORDER BY s.date DESC, s.time ASC
")->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>📅 Schedules</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Schedule</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header"><h3>Schedule List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>
                            <th>Specialization</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📅</div><p>No schedules found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><?= $s['schedule_id'] ?></td>
                            <td><?= $s['date'] ?></td>
                            <td><?= $s['time'] ?></td>
                            <td><?= htmlspecialchars($s['trainer_name']) ?></td>
                            <td><?= htmlspecialchars($s['specialization']) ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $s['schedule_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $s['schedule_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this schedule?')">🗑️ Delete</a>
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
            <h3>Add New Schedule</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="text" name="time" required placeholder="e.g. 07:00 - 08:00">
                </div>
                <div class="form-group" style="grid-column: 1/-1;">
                    <label>Trainer</label>
                    <select name="trainer_id" required>
                        <option value="">-- Select Trainer --</option>
                        <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['trainer_id'] ?>"><?= htmlspecialchars($t['name']) ?> - <?= htmlspecialchars($t['specialization']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Schedule</button>
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
            <h3>Edit Schedule</h3>
            <a href="schedules.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="schedule_id" value="<?= $edit_data['schedule_id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required value="<?= $edit_data['date'] ?>">
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="text" name="time" required value="<?= htmlspecialchars($edit_data['time']) ?>">
                </div>
                <div class="form-group" style="grid-column: 1/-1;">
                    <label>Trainer</label>
                    <select name="trainer_id" required>
                        <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['trainer_id'] ?>" <?= $t['trainer_id'] == $edit_data['trainer_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?> - <?= htmlspecialchars($t['specialization']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Schedule</button>
                <a href="schedules.php" class="btn btn-secondary">Cancel</a>
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
