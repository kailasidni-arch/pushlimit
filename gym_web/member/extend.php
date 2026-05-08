<?php
require_once '../includes/db.php';
require_once 'header.php';

$member_id = $_SESSION['user_id'];
$error = '';
$step = $_SESSION['extend_step'] ?? '1';

if (isset($_GET['back'])) { unset($_SESSION['extend'], $_SESSION['extend_step']); header("Location: extend.php"); exit; }

// Step 1 → 2: select package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '1') {
    $_SESSION['extend'] = ['pkg_id' => $_POST['package_id']];
    $_SESSION['extend_step'] = '2';
    $step = '2';
}

// Step 2: upload proof & save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '2') {
    $ext_data = $_SESSION['extend'] ?? null;
    if ($ext_data) {
        $pay_method = $_POST['pay_method'];
        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== 0) {
            $error = 'Bukti pembayaran wajib diupload!'; $step = '2';
        } else {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($_FILES['proof']['type'], $allowed) || $_FILES['proof']['size'] > 5*1024*1024) {
                $error = 'File harus gambar dan < 5MB!'; $step = '2';
            } else {
                $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
                $proof = 'proof_ext_'.time().'_'.rand(100,999).'.'.$ext;
                if (!is_dir('../uploads/proofs')) mkdir('../uploads/proofs', 0755, true);
                move_uploaded_file($_FILES['proof']['tmp_name'], '../uploads/proofs/'.$proof);

                $pkg = $pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?");
                $pkg->execute([$ext_data['pkg_id']]); $pkg = $pkg->fetch();

                // Record payment (unverified, admin verifies and activates)
                $pdo->prepare("INSERT INTO PAYMENT (member_id,package_id,payment_date,amount,payment_method,proof_file,verified) VALUES(?,?,CURDATE(),?,?,?,0)")
                    ->execute([$member_id, $ext_data['pkg_id'], $pkg['price'], $pay_method, $proof]);

                unset($_SESSION['extend'], $_SESSION['extend_step']);
                $step = 'success';
            }
        }
    }
}

if ($step === '2' && empty($_SESSION['extend'])) $step = '1';
$extData = $_SESSION['extend'] ?? [];
$selectedPkg = null;
if (!empty($extData['pkg_id'])) {
    $sp = $pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?");
    $sp->execute([$extData['pkg_id']]); $selectedPkg = $sp->fetch();
}

$packages = $pdo->query("SELECT * FROM PACKAGE ORDER BY price ASC")->fetchAll();
$member = $pdo->prepare("SELECT m.*, p.package_name, p.duration FROM MEMBER m LEFT JOIN PACKAGE p ON m.package_id=p.package_id WHERE m.member_id=?");
$member->execute([$member_id]); $member = $member->fetch();
?>

<div class="main-content">
    <div class="topbar">
        <h1>🔄 Perpanjang Paket</h1>
    </div>
    <div class="content">

        <!-- Current package info -->
        <div class="card" style="margin-bottom:22px;">
            <div class="card-header"><h3>📦 Paket Aktif Saat Ini</h3></div>
            <div class="card-body">
                <div style="display:flex; gap:32px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:12px; color:var(--text-light); font-weight:600; margin-bottom:3px;">PAKET</div>
                        <div style="font-size:16px; font-weight:700; color:var(--text-dark);"><?= $member['package_name'] ?? 'Belum ada' ?></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-light); font-weight:600; margin-bottom:3px;">DURASI</div>
                        <div style="font-size:16px; font-weight:700; color:var(--text-dark);"><?= $member['duration'] ? $member['duration'].' Bulan' : 'Harian' ?></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:var(--text-light); font-weight:600; margin-bottom:3px;">BERLAKU HINGGA</div>
                        <div style="font-size:16px; font-weight:700; color:<?= $member['package_expiry'] && $member['package_expiry'] < date('Y-m-d') ? '#DC2626' : 'var(--brown-dark)' ?>;">
                            <?= $member['package_expiry'] ? date('d M Y', strtotime($member['package_expiry'])) : '-' ?>
                            <?php if ($member['package_expiry'] && $member['package_expiry'] < date('Y-m-d')): ?>
                            <span class="status-badge status-cancelled" style="margin-left:8px;">Kadaluarsa</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($step === '1'): ?>
        <div class="card">
            <div class="card-header"><h3>Pilih Paket Perpanjangan</h3></div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
                        <?php foreach ($packages as $p): ?>
                        <input type="radio" name="package_id" id="pk<?=$p['package_id']?>" value="<?=$p['package_id']?>" class="pkg-option" required
                            <?= $p['package_id'] == $member['package_id'] ? 'checked' : '' ?>>
                        <label for="pk<?=$p['package_id']?>" class="pkg-label">
                            <div><div class="pkg-name"><?= htmlspecialchars($p['package_name']) ?></div><div class="pkg-dur"><?= $p['duration']==0?'Harian':$p['duration'].' Bulan' ?></div></div>
                            <div class="pkg-price">Rp <?= number_format($p['price'],0,',','.') ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Lanjut ke Pembayaran →</button>
                </form>
            </div>
        </div>

        <?php elseif ($step === '2' && $selectedPkg): ?>
        <div class="card">
            <div class="card-header"><h3>Konfirmasi Pembayaran Perpanjangan</h3></div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-error">⚠️ <?= $error ?></div><?php endif; ?>

                <div style="background:var(--brown-bg); border:1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--border); font-size:13.5px;"><span style="color:var(--text-light);">Paket</span><span style="font-weight:600;"><?= htmlspecialchars($selectedPkg['package_name']) ?></span></div>
                    <div style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--border); font-size:13.5px;"><span style="color:var(--text-light);">Durasi</span><span style="font-weight:600;"><?= $selectedPkg['duration']==0?'Harian':$selectedPkg['duration'].' Bulan' ?></span></div>
                    <div style="display:flex; justify-content:space-between; padding:7px 0; font-size:15px; font-weight:700; color:var(--brown-dark);"><span>Total</span><span>Rp <?= number_format($selectedPkg['price'],0,',','.') ?></span></div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="step" value="2">

                    <div style="font-size:11.5px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:8px;">METODE PEMBAYARAN</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                        <input type="radio" name="pay_method" id="mq" value="QRIS" class="pay-opt" required onchange="sd('qris')"><label for="mq" class="pay-label">📱 QRIS</label>
                        <input type="radio" name="pay_method" id="mb" value="Transfer BNI" class="pay-opt" onchange="sd('bni')"><label for="mb" class="pay-label">🏦 Transfer BNI</label>
                        <input type="radio" name="pay_method" id="md" value="DANA" class="pay-opt" onchange="sd('dana')"><label for="md" class="pay-label">💙 DANA</label>
                        <input type="radio" name="pay_method" id="mg" value="GoPay" class="pay-opt" onchange="sd('gopay')"><label for="mg" class="pay-label">💚 GoPay</label>
                    </div>

                    <div id="d_qris" style="display:none; background:var(--brown-bg); border:1.5px solid var(--brown-pale); border-radius:10px; padding:15px; margin-bottom:14px;">
                        <div style="font-size:12.5px; font-weight:700; color:var(--text-mid); margin-bottom:8px;">📱 Scan QRIS</div>
                        <img src="../uploads/qris.jpeg" style="width:160px; display:block; margin:0 auto; border-radius:8px; border:1px solid var(--border);" alt="QRIS">
                    </div>
                    <div id="d_bni" style="display:none; background:var(--brown-bg); border:1.5px solid var(--brown-pale); border-radius:10px; padding:15px; margin-bottom:14px;">
                        <div style="font-size:12.5px; font-weight:700; color:var(--text-mid); margin-bottom:6px;">🏦 Transfer BNI</div>
                        <div style="font-size:16px; font-weight:700; color:var(--text-dark);">1924182745</div>
                        <div style="font-size:12px; color:var(--text-light);">a.n. GYM PRO</div>
                    </div>
                    <div id="d_dana" style="display:none; background:var(--brown-bg); border:1.5px solid var(--brown-pale); border-radius:10px; padding:15px; margin-bottom:14px;">
                        <div style="font-size:12.5px; font-weight:700; color:var(--text-mid); margin-bottom:6px;">💙 DANA</div>
                        <div style="font-size:16px; font-weight:700; color:var(--text-dark);">082386210045</div>
                        <div style="font-size:12px; color:var(--text-light);">a.n. GYM PRO</div>
                    </div>
                    <div id="d_gopay" style="display:none; background:var(--brown-bg); border:1.5px solid var(--brown-pale); border-radius:10px; padding:15px; margin-bottom:14px;">
                        <div style="font-size:12.5px; font-weight:700; color:var(--text-mid); margin-bottom:6px;">💚 GoPay</div>
                        <div style="font-size:16px; font-weight:700; color:var(--text-dark);">082386210045</div>
                        <div style="font-size:12px; color:var(--text-light);">a.n. GYM PRO</div>
                    </div>

                    <div style="font-size:11.5px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:8px;">UPLOAD BUKTI PEMBAYARAN</div>
                    <div style="border:2px dashed var(--border); border-radius:10px; padding:20px; text-align:center; position:relative; cursor:pointer;" onmouseover="this.style.borderColor='var(--brown-light)'" onmouseout="this.style.borderColor='var(--border)'">
                        <input type="file" name="proof" accept="image/*" onchange="prvw(this)" required style="position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;">
                        <div style="font-size:28px; margin-bottom:6px;">📎</div>
                        <p style="font-size:12px; color:var(--text-light);">Klik untuk upload bukti transfer / screenshot<br><span style="font-size:11px;">JPG, PNG — max 5MB</span></p>
                        <img id="pprev" src="" style="max-width:100%; border-radius:8px; margin-top:10px; display:none;" alt="Preview">
                    </div>

                    <div style="display:flex; gap:10px; margin-top:16px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">✅ Kirim Bukti Pembayaran</button>
                        <a href="extend.php?back=1" class="btn btn-secondary" style="flex:0 0 auto;">← Kembali</a>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($step === 'success'): ?>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:48px 24px;">
                <div style="font-size:56px; margin-bottom:12px;">🎉</div>
                <div style="font-size:20px; font-weight:700; color:var(--text-dark); margin-bottom:8px;">Permintaan Perpanjangan Dikirim!</div>
                <div style="color:var(--text-light); font-size:14px; line-height:1.6; margin-bottom:24px;">Bukti pembayaran sudah diterima.<br>Paket akan diperpanjang setelah admin memverifikasi.</div>
                <a href="index.php" class="btn btn-primary" style="display:inline-block; text-decoration:none; padding:12px 28px;">Kembali ke Dashboard</a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function sd(m){['qris','bni','dana','gopay'].forEach(x=>{document.getElementById('d_'+x).style.display='none';});document.getElementById('d_'+m).style.display='block';}
function prvw(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{const img=document.getElementById('pprev');img.src=e.target.result;img.style.display='block';};r.readAsDataURL(i.files[0]);}}
</script>

<?php require_once '../includes/footer.php'; ?>
