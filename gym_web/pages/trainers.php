<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$message = '';
$edit_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO TRAINER (name, specialization, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['specialization'], $_POST['phone'], $_POST['email']]);
        $message = '<div class="alert alert-success">✅ Trainer added successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE TRAINER SET name=?, specialization=?, phone=?, email=? WHERE trainer_id=?");
        $stmt->execute([$_POST['name'], $_POST['specialization'], $_POST['phone'], $_POST['email'], $_POST['trainer_id']]);
        $message = '<div class="alert alert-success">✅ Trainer updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM TRAINER WHERE trainer_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Trainer deleted successfully!</div>';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM TRAINER WHERE trainer_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$trainers = $pdo->query("SELECT * FROM TRAINER ORDER BY trainer_id DESC")->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>🏋️ Trainers</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Trainer</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header"><h3>Trainer List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trainers)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏋️</div><p>No trainers found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($trainers as $t): ?>
                        <tr>
                            <td><?= $t['trainer_id'] ?></td>
                            <td><?= htmlspecialchars($t['name']) ?></td>
                            <td><?= htmlspecialchars($t['specialization']) ?></td>
                            <td><?= htmlspecialchars($t['phone']) ?></td>
                            <td><?= htmlspecialchars($t['email']) ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $t['trainer_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $t['trainer_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this trainer?')">🗑️ Delete</a>
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
            <h3>Add New Trainer</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Trainer name">
                </div>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" required placeholder="e.g. Weight Training">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required placeholder="Phone number">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Email address">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Trainer</button>
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
            <h3>Edit Trainer</h3>
            <a href="trainers.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="trainer_id" value="<?= $edit_data['trainer_id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name']) ?>">
                </div>
                <div class="form-group">
                    <label>Specialization</label>
                    <input type="text" name="specialization" required value="<?= htmlspecialchars($edit_data['specialization']) ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required value="<?= htmlspecialchars($edit_data['phone']) ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($edit_data['email']) ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Trainer</button>
                <a href="trainers.php" class="btn btn-secondary">Cancel</a>
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
