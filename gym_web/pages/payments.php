<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

$message = '';
$edit_data = null;
$members = $pdo->query("SELECT * FROM MEMBER ORDER BY name")->fetchAll();
$packages = $pdo->query("SELECT * FROM PACKAGE ORDER BY price")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("INSERT INTO PAYMENT (member_id, package_id, payment_date, amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['member_id'], $_POST['package_id'], $_POST['payment_date'], $_POST['amount']]);
        $message = '<div class="alert alert-success">✅ Payment recorded successfully!</div>';
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("UPDATE PAYMENT SET member_id=?, package_id=?, payment_date=?, amount=? WHERE payment_id=?");
        $stmt->execute([$_POST['member_id'], $_POST['package_id'], $_POST['payment_date'], $_POST['amount'], $_POST['payment_id']]);
        $message = '<div class="alert alert-success">✅ Payment updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM PAYMENT WHERE payment_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Payment deleted successfully!</div>';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM PAYMENT WHERE payment_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$payments = $pdo->query("
    SELECT p.payment_id, m.name AS member_name, pk.package_name, p.payment_date, p.amount
    FROM PAYMENT p
    INNER JOIN MEMBER m ON p.member_id = m.member_id
    INNER JOIN PACKAGE pk ON p.package_id = pk.package_id
    ORDER BY p.payment_id DESC
")->fetchAll();

$totalRevenue = $pdo->query("SELECT SUM(amount) FROM PAYMENT")->fetchColumn();
?>

<div class="main-content">
    <div class="topbar">
        <h1>💳 Payments</h1>
        <div class="topbar-right">
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Payment</button>
        </div>
    </div>

    <div class="content">
        <?= $message ?>

        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-icon green">💰</div>
                <div class="stat-info">
                    <h3>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">📄</div>
                <div class="stat-info">
                    <h3><?= count($payments) ?></h3>
                    <p>Total Transactions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📊</div>
                <div class="stat-info">
                    <h3>Rp <?= count($payments) > 0 ? number_format($totalRevenue / count($payments), 0, ',', '.') : 0 ?></h3>
                    <p>Avg. Transaction</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Payment List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Package</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">💳</div><p>No payments found.</p></div></td></tr>
                        <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= $p['payment_id'] ?></td>
                            <td><?= htmlspecialchars($p['member_name']) ?></td>
                            <td><?= htmlspecialchars($p['package_name']) ?></td>
                            <td><?= $p['payment_date'] ?></td>
                            <td>Rp <?= number_format($p['amount'], 0, ',', '.') ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="?edit=<?= $p['payment_id'] ?>" class="btn btn-edit">✏️ Edit</a>
                                <a href="?delete=<?= $p['payment_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this payment?')">🗑️ Delete</a>
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
            <h3>Add New Payment</h3>
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
                    <label>Package</label>
                    <select name="package_id" required onchange="fillAmount(this)">
                        <option value="">-- Select Package --</option>
                        <?php foreach ($packages as $pk): ?>
                        <option value="<?= $pk['package_id'] ?>" data-price="<?= $pk['price'] ?>"><?= htmlspecialchars($pk['package_name']) ?> - Rp <?= number_format($pk['price'], 0, ',', '.') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Amount (Rp)</label>
                    <input type="number" name="amount" id="amountField" required placeholder="Auto-filled from package">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save Payment</button>
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
            <h3>Edit Payment</h3>
            <a href="payments.php" class="modal-close">✕</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="payment_id" value="<?= $edit_data['payment_id'] ?>">
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
                    <label>Package</label>
                    <select name="package_id" required>
                        <?php foreach ($packages as $pk): ?>
                        <option value="<?= $pk['package_id'] ?>" <?= $pk['package_id'] == $edit_data['package_id'] ? 'selected' : '' ?>><?= htmlspecialchars($pk['package_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" required value="<?= $edit_data['payment_date'] ?>">
                </div>
                <div class="form-group">
                    <label>Amount (Rp)</label>
                    <input type="number" name="amount" required value="<?= $edit_data['amount'] ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Update Payment</button>
                <a href="payments.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function fillAmount(sel) {
    const price = sel.options[sel.selectedIndex].dataset.price;
    if (price) document.getElementById('amountField').value = price;
}
</script>

<?php require_once '../includes/footer.php'; ?>
