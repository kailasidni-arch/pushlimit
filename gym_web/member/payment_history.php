<?php
require_once '../includes/db.php';
require_once 'header.php';
$member_id = $_SESSION['user_id'];

$payments = $pdo->prepare("SELECT p.*, pk.package_name, pk.duration FROM PAYMENT p JOIN PACKAGE pk ON p.package_id=pk.package_id WHERE p.member_id=? ORDER BY p.payment_date DESC");
$payments->execute([$member_id]); $payments = $payments->fetchAll();
$total = array_sum(array_column($payments,'amount'));
?>
<div class="main-content">
    <div class="topbar"><h1>💳 Riwayat Pembayaran</h1></div>
    <div class="content">
        <div class="stat-card" style="margin-bottom:24px;max-width:280px;">
            <div class="stat-icon green">💰</div>
            <div class="stat-info"><h3>Rp <?=number_format($total,0,',','.')?></h3><p>Total Pembayaran</p></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Semua Transaksi</h3></div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>#</th><th>Paket</th><th>Metode</th><th>Tanggal</th><th>Jumlah</th><th>Bukti</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if(empty($payments)): ?>
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💳</div><p>Belum ada transaksi.</p></div></td></tr>
                    <?php else: ?>
                    <?php foreach($payments as $p): ?>
                    <tr>
                        <td><?=$p['payment_id']?></td>
                        <td><?=htmlspecialchars($p['package_name'])?></td>
                        <td style="font-size:12px;"><?=htmlspecialchars($p['payment_method']??'-')?></td>
                        <td><?=date('d M Y',strtotime($p['payment_date']))?></td>
                        <td>Rp <?=number_format($p['amount'],0,',','.')?></td>
                        <td>
                            <?php if($p['proof_file']): ?>
                            <img src="../uploads/proofs/<?=$p['proof_file']?>" class="proof-thumb" onclick="showProof('../uploads/proofs/<?=$p['proof_file']?>')" alt="Bukti">
                            <?php else: ?><span style="font-size:12px;color:var(--text-light);">-</span><?php endif; ?>
                        </td>
                        <td><?php if($p['verified']): ?><span class="status-badge status-confirmed">✅ Verified</span><?php else: ?><span class="status-badge status-pending">⏳ Menunggu</span><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- PROOF MODAL -->
<div class="modal-overlay" id="proofModal">
    <div class="modal" style="max-width:440px;text-align:center;">
        <div class="modal-header"><h3>Bukti Pembayaran</h3><button class="modal-close" onclick="closeModal('proofModal')">✕</button></div>
        <img id="proofImg" src="" style="max-width:100%;border-radius:10px;border:1px solid var(--border);" alt="Bukti">
    </div>
</div>
<script>
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
function showProof(src){document.getElementById('proofImg').src=src;openModal('proofModal');}
</script>
<?php require_once '../includes/footer.php'; ?>
