<?php
require_once '../includes/db.php';
require_once 'header.php';
$trainer_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE TRAINER SET name=?, phone=?, specialization=?, password=? WHERE trainer_id=?")->execute([$name, $phone, $specialization, $hashed, $trainer_id]);
    } else {
        $pdo->prepare("UPDATE TRAINER SET name=?, phone=?, specialization=? WHERE trainer_id=?")->execute([$name, $phone, $specialization, $trainer_id]);
    }
    $_SESSION['name'] = $name;
    $message = '<div class="alert alert-success">✅ Profil diperbarui!</div>';
}

$trainer = $pdo->prepare("SELECT * FROM TRAINER WHERE trainer_id=?");
$trainer->execute([$trainer_id]);
$trainer = $trainer->fetch();
?>

<div class="main-content">
    <div class="topbar"><h1>👤 Profil Saya</h1></div>
    <div class="content">
        <?= $message ?>
        <div class="card" style="max-width:520px;">
            <div class="card-header"><h3>Edit Profil Trainer</h3></div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" required value="<?= htmlspecialchars($trainer['name']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Email (tidak bisa diubah)</label>
                            <input type="email" value="<?= htmlspecialchars($trainer['email']) ?>" disabled style="background:#f5f5f5;">
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="phone" required value="<?= htmlspecialchars($trainer['phone']) ?>">
                        </div>
                        <div class="form-group full">
                            <label>Spesialisasi</label>
                            <input type="text" name="specialization" required value="<?= htmlspecialchars($trainer['specialization']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Password Baru (kosongkan jika tidak ingin ubah)</label>
                            <input type="password" name="new_password" placeholder="Password baru" minlength="6">
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
