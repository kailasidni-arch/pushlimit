<?php
require_once '../includes/db.php';
require_once 'header.php';

$message = '';

if (isset($_GET['verify'])) {
    $payment_id = (int)$_GET['verify'];
    $pay = $pdo->prepare("SELECT * FROM PAYMENT WHERE payment_id=?");
    $pay->execute([$payment_id]); $pay = $pay->fetch();
    if ($pay) {
        $pdo->prepare("UPDATE PAYMENT SET verified=1 WHERE payment_id=?")->execute([$payment_id]);
        $pkg = $pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?");
        $pkg->execute([$pay['package_id']]); $pkg = $pkg->fetch();
        $newExpiry = $pkg['duration'] > 0 ? date('Y-m-d', strtotime("+{$pkg['duration']} months")) : date('Y-m-d');
        $pdo->prepare("UPDATE MEMBER SET status='active', package_id=?, package_expiry=? WHERE member_id=?")
            ->execute([$pay['package_id'], $newExpiry, $pay['member_id']]);
        $message = '<div class="alert alert-success">✅ Pembayaran diverifikasi! Akun member diaktifkan.</div>';
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM PAYMENT WHERE payment_id=?")->execute([$_GET['delete']]);
    $message = '<div class="alert alert-success">🗑️ Pembayaran dihapus.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $proof = null;
    if (isset($_FILES['proof']) && $_FILES['proof']['error'] === 0) {
        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $proof = 'proof_'.time().'_'.rand(100,999).'.'.$ext;
        if (!is_dir('../uploads/proofs')) mkdir('../uploads/proofs', 0755, true);
        move_uploaded_file($_FILES['proof']['tmp_name'], '../uploads/proofs/'.$proof);
    }
    $pdo->prepare("INSERT INTO PAYMENT (member_id,package_id,payment_date,amount,payment_method,proof_file,verified) VALUES(?,?,?,?,?,?,1)")
        ->execute([$_POST['member_id'], $_POST['package_id'], $_POST['payment_date'], $_POST['amount'], $_POST['payment_method'], $proof]);
    $message = '<div class="alert alert-success">✅ Pembayaran ditambahkan!</div>';
}

$payments = $pdo->query("SELECT p.*, m.name AS member_name, pk.package_name FROM PAYMENT p INNER JOIN MEMBER m ON p.member_id=m.member_id INNER JOIN PACKAGE pk ON p.package_id=pk.package_id ORDER BY p.verified ASC, p.payment_id DESC")->fetchAll();
$members = $pdo->query("SELECT * FROM MEMBER ORDER BY name")->fetchAll();
$packages = $pdo->query("SELECT * FROM PACKAGE ORDER BY price")->fetchAll();
$totalRevenue = $pdo->query("SELECT SUM(amount) FROM PAYMENT WHERE verified=1")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM PAYMENT WHERE verified=0")->fetchColumn();
?>

<div class="main-content">
    <div class="topbar">
        <h1>💳 Payments</h1>
        <div class="topbar-right">
            <?php if($pendingCount>0): ?>
            <span style="background:rgba(245,166,35,0.12);color:#FBBF24;border:1px solid rgba(245,166,35,0.35);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;">⏳ <?=$pendingCount?> pending</span>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Payment</button>
        </div>
    </div>
    <div class="content">
        <?= $message ?>
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-info"><h3>Rp <?=number_format($totalRevenue,0,',','.')?></h3><p>Total Revenue</p></div></div>
            <div class="stat-card"><div class="stat-icon blue">📄</div><div class="stat-info"><h3><?=count($payments)?></h3><p>Total Transaksi</p></div></div>
            <div class="stat-card"><div class="stat-icon orange">⏳</div><div class="stat-info"><h3><?=$pendingCount?></h3><p>Menunggu Verifikasi</p></div></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Payment List</h3></div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>#</th><th>Member</th><th>Package</th><th>Method</th><th>Date</th><th>Amount</th><th>Bukti</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach($payments as $p): ?>
                    <tr style="<?=!$p['verified']?'background:rgba(245,166,35,0.06);':''?>">
                        <td><?=$p['payment_id']?></td>
                        <td><?=htmlspecialchars($p['member_name'])?></td>
                        <td><?=htmlspecialchars($p['package_name'])?></td>
                        <td style="font-size:12px;"><?=htmlspecialchars($p['payment_method']??'-')?></td>
                        <td><?=$p['payment_date']?></td>
                        <td>Rp <?=number_format($p['amount'],0,',','.')?></td>
                        <td>
                            <?php if($p['proof_file']): ?>
                            <img src="../uploads/proofs/<?=$p['proof_file']?>" class="proof-thumb" onclick="showProof('../uploads/proofs/<?=$p['proof_file']?>')" alt="Bukti">
                            <?php else: ?><span style="color:var(--text-light);font-size:12px;">-</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if($p['verified']): ?>
                            <span class="status-badge status-confirmed">✅ Verified</span>
                            <?php else: ?>
                            <span class="status-badge status-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="display:flex;gap:5px;flex-wrap:wrap;">
                            <?php if(!$p['verified']): ?>
                            <a href="?verify=<?=$p['payment_id']?>" class="btn btn-success" onclick="return confirm('Verifikasi pembayaran ini?')">✅ Verify</a>
                            <?php endif; ?>
                            <a href="?delete=<?=$p['payment_id']?>" class="btn btn-delete" onclick="return confirm('Hapus?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($payments)): ?>
                    <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">💳</div><p>No payments.</p></div></td></tr>
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
        <div class="modal-header"><h3>Add Payment</h3><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div class="form-group"><label>Member</label><select name="member_id" required><option value="">-- Select --</option><?php foreach($members as $m): ?><option value="<?=$m['member_id']?>"><?=htmlspecialchars($m['name'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Package</label><select name="package_id" required onchange="fillAmt(this)"><option value="">-- Select --</option><?php foreach($packages as $pk): ?><option value="<?=$pk['package_id']?>" data-price="<?=$pk['price']?>"><?=htmlspecialchars($pk['package_name'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Date</label><input type="date" name="payment_date" required value="<?=date('Y-m-d')?>"></div>
                <div class="form-group"><label>Amount</label><input type="number" name="amount" id="amtField" required></div>
                <div class="form-group"><label>Method</label><select name="payment_method"><option value="QRIS">QRIS</option><option value="Transfer BNI">Transfer BNI</option><option value="DANA">DANA</option><option value="GoPay">GoPay</option><option value="Manual">Manual</option></select></div>
                <div class="form-group"><label>Bukti (opsional)</label><input type="file" name="proof" accept="image/*"></div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- PROOF MODAL -->
<div class="modal-overlay" id="proofModal">
    <div class="modal" style="max-width:440px;text-align:center;">
        <div class="modal-header"><h3>Bukti Pembayaran</h3><button class="modal-close" onclick="closeModal('proofModal')">✕</button></div>
        <img id="proofImg" src="" style="max-width:100%;border-radius:10px;border:1px solid var(--border);" alt="Bukti">
        <div style="margin-top:14px;"><a id="proofDl" href="" download class="btn btn-secondary btn-sm">⬇️ Download</a></div>
    </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function fillAmt(s){const p=s.options[s.selectedIndex].dataset.price;if(p)document.getElementById('amtField').value=p;}
function showProof(src){document.getElementById('proofImg').src=src;document.getElementById('proofDl').href=src;openModal('proofModal');}
</script>
<?php require_once '../includes/footer.php'; ?>
