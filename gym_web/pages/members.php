<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$message = '';
$edit_data = null;

// INSERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO MEMBER (name, address, phone) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['address'], $_POST['phone']]);
        $message = '<div class="alert alert-success">✅ Member added successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE MEMBER SET name=?, address=?, phone=? WHERE member_id=?");
        $stmt->execute([$_POST['name'], $_POST['address'], $_POST['phone'], $_POST['member_id']]);
        $message = '<div class="alert alert-success">✅ Member updated successfully!</div>';
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM MEMBER WHERE member_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Member deleted successfully!</div>';
}

// EDIT FETCH
if (isset($_GET['edit'])) {
    $edit_data = $pdo->prepare("SELECT * FROM MEMBER WHERE member_id=?");
    $edit_data->execute([$_GET['edit']]);
    $edit_data = $edit_data->fetch();
}

// SEARCH
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM MEMBER WHERE name LIKE ? OR phone LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $members = $stmt->fetchAll();
} else {
    $members = $pdo->query("SELECT * FROM MEMBER ORDER BY member_id DESC")->fetchAll();
}
?>

<div class="main-content">
    <div class="topbar">
        <h1>👥 Members</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Member</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>

        <!-- Search -->
        <div class="card">
            <div class="card-header">
                <h3>Member List</h3>
                <form method="GET" style="display:flex; gap:10px;">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" placeholder="Search member..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
                    <?php if($search): ?>
                        <a href="members.php" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">👥</div><p>No members found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= $m['member_id'] ?></td>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['address']) ?></td>
                            <td><?= htmlspecialchars($m['phone']) ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $m['member_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $m['member_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this member?')">🗑️ Delete</a>
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
            <h3>Add New Member</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required placeholder="Enter phone number">
                </div>
                <div class="form-group" style="grid-column: 1/-1;">
                    <label>Address</label>
                    <textarea name="address" required rows="2" placeholder="Enter address"></textarea>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Member</button>
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
            <h3>Edit Member</h3>
            <a href="members.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="member_id" value="<?= $edit_data['member_id'] ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name']) ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required value="<?= htmlspecialchars($edit_data['phone']) ?>">
                </div>
                <div class="form-group" style="grid-column: 1/-1;">
                    <label>Address</label>
                    <textarea name="address" required rows="2"><?= htmlspecialchars($edit_data['address']) ?></textarea>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Member</button>
                <a href="members.php" class="btn btn-secondary">Cancel</a>
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
