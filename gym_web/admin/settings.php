<?php
require_once '../includes/db.php';
require_once 'header.php';

$message = '';

// Create settings table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS GYM_SETTINGS (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT)");

// Load current settings
function getSetting($pdo, $key, $default = '') {
    $s = $pdo->prepare("SELECT setting_value FROM GYM_SETTINGS WHERE setting_key=?");
    $s->execute([$key]); $r = $s->fetch();
    return $r ? $r['setting_value'] : $default;
}

function saveSetting($pdo, $key, $value) {
    $pdo->prepare("INSERT INTO GYM_SETTINGS (setting_key, setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $value, $value]);
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    $fields = ['gym_name','gym_address','gym_phone','gym_email','gym_lat','gym_lng','bank_name','bank_account','bank_holder','dana_number','dana_holder','gopay_number','gopay_holder'];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) saveSetting($pdo, $f, trim($_POST[$f]));
    }

    // Handle QRIS upload
    if (isset($_FILES['qris_image']) && $_FILES['qris_image']['error'] === 0) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['qris_image']['type'], $allowed)) {
            $ext = pathinfo($_FILES['qris_image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['qris_image']['tmp_name'], '../uploads/qris.'.$ext);
            saveSetting($pdo, 'qris_file', 'qris.'.$ext);
        }
    }
    $message = '<div class="alert alert-success">✅ Pengaturan berhasil disimpan!</div>';
}

// RESET DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset') {
    $target = $_POST['reset_target'];
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        if ($target === 'bookings' || $target === 'all') {
            $pdo->exec("TRUNCATE TABLE BOOKING");
        }
        if ($target === 'payments' || $target === 'all') {
            $pdo->exec("TRUNCATE TABLE PAYMENT");
        }
        if ($target === 'members' || $target === 'all') {
            $pdo->exec("TRUNCATE TABLE BOOKING");
            $pdo->exec("TRUNCATE TABLE PAYMENT");
            $pdo->exec("TRUNCATE TABLE MEMBER");
        }
        if ($target === 'schedules') {
            $pdo->exec("TRUNCATE TABLE BOOKING");
            $pdo->exec("TRUNCATE TABLE SCHEDULE");
        }
        if ($target === 'all') {
            $pdo->exec("TRUNCATE TABLE BOOKING");
            $pdo->exec("TRUNCATE TABLE PAYMENT");
            $pdo->exec("TRUNCATE TABLE MEMBER");
            $pdo->exec("TRUNCATE TABLE SCHEDULE");
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        $message = '<div class="alert alert-success">✅ Data berhasil direset!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-error">❌ Error: '.$e->getMessage().'</div>';
    }
}

// Load all settings
$s = [
    'gym_name'     => getSetting($pdo, 'gym_name', 'GymPro'),
    'gym_address'  => getSetting($pdo, 'gym_address', 'Jl. Sudirman No. 88, Jakarta'),
    'gym_phone'    => getSetting($pdo, 'gym_phone', '(021) 5555-1234'),
    'gym_email'    => getSetting($pdo, 'gym_email', 'info@gympro.id'),
    'gym_lat'      => getSetting($pdo, 'gym_lat', '-6.2175'),
    'gym_lng'      => getSetting($pdo, 'gym_lng', '106.8050'),
    'bank_name'    => getSetting($pdo, 'bank_name', 'BNI'),
    'bank_account' => getSetting($pdo, 'bank_account', '1924182745'),
    'bank_holder'  => getSetting($pdo, 'bank_holder', 'GYM PRO'),
    'dana_number'  => getSetting($pdo, 'dana_number', '082386210045'),
    'dana_holder'  => getSetting($pdo, 'dana_holder', 'GYM PRO'),
    'gopay_number' => getSetting($pdo, 'gopay_number', '082386210045'),
    'gopay_holder' => getSetting($pdo, 'gopay_holder', 'GYM PRO'),
];

// Count data
$counts = [
    'members'   => $pdo->query("SELECT COUNT(*) FROM MEMBER")->fetchColumn(),
    'bookings'  => $pdo->query("SELECT COUNT(*) FROM BOOKING")->fetchColumn(),
    'payments'  => $pdo->query("SELECT COUNT(*) FROM PAYMENT")->fetchColumn(),
    'schedules' => $pdo->query("SELECT COUNT(*) FROM SCHEDULE")->fetchColumn(),
];
?>

<div class="main-content">
    <div class="topbar"><h1>⚙️ Settings</h1></div>
    <div class="content">
        <?= $message ?>

        <div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

            <!-- LEFT: Settings Form -->
            <div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_settings">

                    <!-- Gym Info -->
                    <div class="card">
                        <div class="card-header"><h3>🏢 Informasi Gym</h3></div>
                        <div class="card-body">
                            <div class="form-grid">
                                <div class="form-group full">
                                    <label>Nama Gym</label>
                                    <input type="text" name="gym_name" value="<?= htmlspecialchars($s['gym_name']) ?>" required>
                                </div>
                                <div class="form-group full">
                                    <label>Alamat Lengkap</label>
                                    <textarea name="gym_address" rows="2" style="padding:10px 13px; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; width:100%;"><?= htmlspecialchars($s['gym_address']) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" name="gym_phone" value="<?= htmlspecialchars($s['gym_phone']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email Gym</label>
                                    <input type="email" name="gym_email" value="<?= htmlspecialchars($s['gym_email']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GPS Location -->
                    <div class="card">
                        <div class="card-header"><h3>📍 Koordinat Lokasi GPS</h3></div>
                        <div class="card-body">
                            <p style="font-size:13px; color:var(--text-light); margin-bottom:14px;">Cari koordinat di <a href="https://www.google.com/maps" target="_blank" style="color:var(--brown-dark);">Google Maps</a> → klik kanan lokasi → salin koordinat</p>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Latitude</label>
                                    <input type="text" name="gym_lat" value="<?= htmlspecialchars($s['gym_lat']) ?>" placeholder="-6.2175">
                                </div>
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input type="text" name="gym_lng" value="<?= htmlspecialchars($s['gym_lng']) ?>" placeholder="106.8050">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="card">
                        <div class="card-header"><h3>💳 Info Pembayaran</h3></div>
                        <div class="card-body">
                            <div style="font-size:12px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:10px;">🏦 BANK TRANSFER</div>
                            <div class="form-grid" style="margin-bottom:18px;">
                                <div class="form-group">
                                    <label>Nama Bank</label>
                                    <input type="text" name="bank_name" value="<?= htmlspecialchars($s['bank_name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>No. Rekening</label>
                                    <input type="text" name="bank_account" value="<?= htmlspecialchars($s['bank_account']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Atas Nama</label>
                                    <input type="text" name="bank_holder" value="<?= htmlspecialchars($s['bank_holder']) ?>">
                                </div>
                            </div>

                            <div style="font-size:12px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:10px;">💙 DANA</div>
                            <div class="form-grid" style="margin-bottom:18px;">
                                <div class="form-group">
                                    <label>No. DANA</label>
                                    <input type="text" name="dana_number" value="<?= htmlspecialchars($s['dana_number']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Atas Nama</label>
                                    <input type="text" name="dana_holder" value="<?= htmlspecialchars($s['dana_holder']) ?>">
                                </div>
                            </div>

                            <div style="font-size:12px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:10px;">💚 GOPAY</div>
                            <div class="form-grid" style="margin-bottom:18px;">
                                <div class="form-group">
                                    <label>No. GoPay</label>
                                    <input type="text" name="gopay_number" value="<?= htmlspecialchars($s['gopay_number']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Atas Nama</label>
                                    <input type="text" name="gopay_holder" value="<?= htmlspecialchars($s['gopay_holder']) ?>">
                                </div>
                            </div>

                            <div style="font-size:12px; font-weight:700; color:var(--text-mid); letter-spacing:.3px; margin-bottom:10px;">📱 QRIS</div>
                            <div class="form-group">
                                <label>Ganti Gambar QRIS</label>
                                <input type="file" name="qris_image" accept="image/*">
                                <?php $qris = getSetting($pdo,'qris_file','qris.jpeg'); ?>
                                <img src="../uploads/<?= $qris ?>" style="width:120px; margin-top:8px; border-radius:8px; border:1px solid var(--border);" alt="QRIS">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">💾 Simpan Semua Pengaturan</button>
                </form>
            </div>

            <!-- RIGHT: Reset Data -->
            <div>
                <div class="card">
                    <div class="card-header"><h3>🗑️ Reset Data</h3></div>
                    <div class="card-body">
                        <div class="alert alert-warning" style="margin-bottom:16px;">⚠️ Data yang direset tidak bisa dikembalikan!</div>

                        <!-- Data counts -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
                            <?php foreach($counts as $label => $count): ?>
                            <div style="background:var(--brown-bg); border:1px solid var(--border); border-radius:10px; padding:12px; text-align:center;">
                                <div style="font-size:20px; font-weight:700; color:var(--brown-dark);"><?= $count ?></div>
                                <div style="font-size:11px; color:var(--text-light); text-transform:capitalize; margin-top:2px;"><?= $label ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" onsubmit="return confirmReset(this)">
                            <input type="hidden" name="action" value="reset">
                            <div class="form-group" style="margin-bottom:14px;">
                                <label>Pilih Data yang Direset</label>
                                <select name="reset_target" required style="padding:10px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13.5px; width:100%;">
                                    <option value="">-- Pilih --</option>
                                    <option value="bookings">Bookings only (<?= $counts['bookings'] ?> data)</option>
                                    <option value="payments">Payments only (<?= $counts['payments'] ?> data)</option>
                                    <option value="schedules">Schedules + Bookings (<?= $counts['schedules'] ?> data)</option>
                                    <option value="members">Members + Bookings + Payments</option>
                                    <option value="all">⚠️ RESET SEMUA (kecuali trainer & package)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-delete" style="width:100%; padding:11px; font-size:14px;">🗑️ Reset Data</button>
                        </form>
                    </div>
                </div>

                <!-- Admin Password Change -->
                <div class="card">
                    <div class="card-header"><h3>🔐 Ganti Password Admin</h3></div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_pass') {
                            $admin_id = $_SESSION['user_id'];
                            $old = $_POST['old_password'];
                            $new = $_POST['new_password'];
                            $adm = $pdo->prepare("SELECT * FROM ADMIN WHERE admin_id=?");
                            $adm->execute([$admin_id]); $adm = $adm->fetch();
                            if ($adm && password_verify($old, $adm['password'])) {
                                $pdo->prepare("UPDATE ADMIN SET password=? WHERE admin_id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $admin_id]);
                                echo '<div class="alert alert-success">✅ Password berhasil diubah!</div>';
                            } else {
                                echo '<div class="alert alert-error">❌ Password lama salah!</div>';
                            }
                        }
                        ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_pass">
                            <div class="form-group" style="margin-bottom:12px;">
                                <label>Password Lama</label>
                                <input type="password" name="old_password" required placeholder="Password saat ini">
                            </div>
                            <div class="form-group" style="margin-bottom:14px;">
                                <label>Password Baru</label>
                                <input type="password" name="new_password" required placeholder="Password baru" minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%;">🔐 Ganti Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmReset(form) {
    const target = form.reset_target.value;
    if (!target) { alert('Pilih data yang ingin direset!'); return false; }
    return confirm('⚠️ YAKIN ingin mereset data "' + target.toUpperCase() + '"?\n\nData tidak bisa dikembalikan!');
}
</script>

<?php require_once '../includes/footer.php'; ?>
