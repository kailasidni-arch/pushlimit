<?php
require_once '../includes/db.php';
require_once 'header.php';
$member_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE MEMBER SET name=?, phone=?, address=?, password=? WHERE member_id=?")->execute([$name, $phone, $address, $hashed, $member_id]);
    } else {
        $pdo->prepare("UPDATE MEMBER SET name=?, phone=?, address=? WHERE member_id=?")->execute([$name, $phone, $address, $member_id]);
    }
    $_SESSION['name'] = $name;
    $message = '<div class="alert alert-success">✅ Profil berhasil diperbarui!</div>';
}

$member = $pdo->prepare("SELECT * FROM MEMBER WHERE member_id = ?");
$member->execute([$member_id]);
$member = $member->fetch();
?>

<div class="main-content">
    <div class="topbar"><h1>👤 Edit Profil</h1></div>
    <div class="content">
        <?= $message ?>
        <div class="card" style="max-width:560px;">
            <div class="card-header"><h3>Informasi Profil</h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($member['name']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Email (tidak bisa diubah)</label>
                            <input type="email" value="<?= htmlspecialchars($member['email']) ?>" disabled style="background:#f5f5f5;">
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="phone" required value="<?= htmlspecialchars($member['phone']) ?>">
                        </div>
                        <div class="form-group full">
                            <label>Alamat</label>
                            <textarea name="address" required rows="2"><?= htmlspecialchars($member['address']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Password Baru (kosongkan jika tidak ingin ubah)</label>
                            <input type="password" name="new_password" placeholder="Password baru" minlength="6">
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
