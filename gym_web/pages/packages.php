<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$message = '';
$edit_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO PACKAGE (package_name, price, duration) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['package_name'], $_POST['price'], $_POST['duration']]);
        $message = '<div class="alert alert-success">✅ Package added successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE PACKAGE SET package_name=?, price=?, duration=? WHERE package_id=?");
        $stmt->execute([$_POST['package_name'], $_POST['price'], $_POST['duration'], $_POST['package_id']]);
        $message = '<div class="alert alert-success">✅ Package updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM PACKAGE WHERE package_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Package deleted successfully!</div>';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$packages = $pdo->query("SELECT * FROM PACKAGE ORDER BY price ASC")->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>📦 Packages</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Package</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>
        <div class="card">
            <div class="card-header"><h3>Package List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Package Name</th>
                            <th>Price</th>
                            <th>Duration (months)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($packages)): ?>
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">📦</div><p>No packages found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><?= $p['package_id'] ?></td>
                            <td><?= htmlspecialchars($p['package_name']) ?></td>
                            <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td><?= $p['duration'] == 0 ? 'Daily' : $p['duration'].' month(s)' ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $p['package_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $p['package_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this package?')">🗑️ Delete</a>
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
            <h3>Add New Package</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="package_name" required placeholder="e.g. Gold">
                </div>
                <div class="form-group">
                    <label>Price (Rp)</label>
                    <input type="number" name="price" required placeholder="e.g. 500000">
                </div>
                <div class="form-group">
                    <label>Duration (months, 0 = daily)</label>
                    <input type="number" name="duration" required placeholder="e.g. 3">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Package</button>
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
            <h3>Edit Package</h3>
            <a href="packages.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="package_id" value="<?= $edit_data['package_id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="package_name" required value="<?= htmlspecialchars($edit_data['package_name']) ?>">
                </div>
                <div class="form-group">
                    <label>Price (Rp)</label>
                    <input type="number" name="price" required value="<?= $edit_data['price'] ?>">
                </div>
                <div class="form-group">
                    <label>Duration (months)</label>
                    <input type="number" name="duration" required value="<?= $edit_data['duration'] ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Package</button>
                <a href="packages.php" class="btn btn-secondary">Cancel</a>
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
