<?php
require_once '../includes/db.php';
require_once 'header.php';

$member_id = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (in_array($file['type'],$allowed) && $file['size']<2*1024*1024) {
        $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
        $filename='member_'.$member_id.'.'.$ext;
        $uploadPath='../uploads/photos/'.$filename;
        if (!is_dir('../uploads/photos')) mkdir('../uploads/photos',0755,true);
        if (move_uploaded_file($file['tmp_name'],$uploadPath)) {
            $pdo->prepare("UPDATE MEMBER SET photo=? WHERE member_id=?")->execute([$filename,$member_id]);
            $message='success';
        }
    } else { $message='error'; }
}

$member=$pdo->prepare("SELECT m.*,p.package_name,p.duration FROM MEMBER m LEFT JOIN PACKAGE p ON m.package_id=p.package_id WHERE m.member_id=?");
$member->execute([$member_id]);
$member=$member->fetch();

$photoPath=$member['photo']?'../uploads/photos/'.$member['photo']:'';
$memberCode='GYM-'.str_pad($member_id,5,'0',STR_PAD_LEFT);
$isExpired = $member['package_expiry'] && strtotime($member['package_expiry']) < time();
?>

<div class="main-content">
    <div class="topbar">
        <h1>🪪 Kartu Member</h1>
        <div class="topbar-right"><span class="badge-date">📅 <?= date('d M Y') ?></span></div>
    </div>
    <div class="content">
        <?php if($message==='success'): ?><div class="alert alert-success">✅ Foto berhasil diperbarui!</div><?php endif; ?>
        <?php if($message==='error'): ?><div class="alert alert-error">❌ Gagal upload. Pastikan file gambar dan < 2MB.</div><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

            <!-- E-CARD -->
            <div>
                <div class="card-header" style="padding:0 0 14px;background:none;border:none;"><h3>Preview Kartu Member</h3></div>

                <div id="memberCard" style="
                    background: linear-gradient(135deg, #0f0f0f 0%, #1a0a05 50%, #1a0505 100%);
                    border-radius:16px; padding:26px; color:#fff; position:relative;
                    overflow:hidden; box-shadow:0 16px 48px rgba(0,0,0,0.6),0 0 0 1px rgba(232,56,13,0.2);
                    min-height:210px;
                ">
                    <!-- Glow blobs -->
                    <div style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;background:radial-gradient(circle,rgba(232,56,13,0.18) 0%,transparent 70%);pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-40px;left:-20px;width:140px;height:140px;background:radial-gradient(circle,rgba(255,100,50,0.08) 0%,transparent 70%);pointer-events:none;"></div>
                    <!-- Top red accent line -->
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#E8380D,#FF6B35,transparent);border-radius:16px 16px 0 0;"></div>

                    <!-- Header row -->
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;">
                        <div>
                            <div style="font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:3px;line-height:1;color:#fff;">GYM<span style="color:#E8380D;">PRO</span></div>
                            <div style="font-size:9px;color:rgba(255,255,255,0.4);letter-spacing:3px;text-transform:uppercase;margin-top:3px;">Member Card</div>
                        </div>
                        <div style="background:<?= $isExpired?'rgba(239,68,68,0.2)':'rgba(232,56,13,0.2)' ?>;border:1px solid <?= $isExpired?'rgba(239,68,68,0.4)':'rgba(232,56,13,0.4)' ?>;padding:4px 12px;border-radius:4px;font-size:10px;font-weight:800;color:<?= $isExpired?'#F87171':'#FF6B35' ?>;letter-spacing:1.5px;text-transform:uppercase;">
                            <?= strtoupper($member['package_name']??'NO PACKAGE') ?>
                        </div>
                    </div>

                    <!-- Member info -->
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                        <?php
                        $avatarSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23272727'/%3E%3Ccircle cx='50' cy='38' r='18' fill='%23444'/%3E%3Cellipse cx='50' cy='90' rx='30' ry='22' fill='%23444'/%3E%3C/svg%3E";
                        $imgSrc = ($photoPath && file_exists($photoPath)) ? $photoPath : $avatarSvg;
                        ?>
                        <img src="<?= $imgSrc ?>" onerror="this.src='<?= $avatarSvg ?>'"
                            style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid rgba(232,56,13,0.5);flex-shrink:0;">
                        <div>
                            <div style="font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:800;letter-spacing:0.5px;line-height:1.1;"><?= htmlspecialchars($member['name']) ?></div>
                            <div style="font-size:11.5px;color:rgba(255,255,255,0.5);margin-top:4px;"><?= htmlspecialchars($member['email']) ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:2px;"><?= htmlspecialchars($member['phone']) ?></div>
                        </div>
                    </div>

                    <!-- Footer row -->
                    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,0.08);padding-top:14px;">
                        <div>
                            <div style="font-size:9px;color:rgba(255,255,255,0.35);letter-spacing:2px;text-transform:uppercase;">Member ID</div>
                            <div style="font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:800;letter-spacing:3px;color:#fff;margin-top:2px;"><?= $memberCode ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:9px;color:rgba(255,255,255,0.35);letter-spacing:2px;text-transform:uppercase;">Expired</div>
                            <div style="font-size:13px;font-weight:700;color:<?= $isExpired?'#F87171':'rgba(255,255,255,0.85)' ?>;margin-top:2px;"><?= $member['package_expiry']?date('d M Y',strtotime($member['package_expiry'])):'-' ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:9px;color:rgba(255,255,255,0.35);letter-spacing:2px;text-transform:uppercase;">Status</div>
                            <span class="status-badge status-<?= $member['status'] ?>" style="margin-top:4px;display:inline-block;"><?= ucfirst($member['status']) ?></span>
                        </div>
                    </div>
                </div>
                <p style="font-size:12px;color:rgba(255,255,255,0.3);text-align:center;margin-top:12px;font-weight:500;">* Screenshot untuk menyimpan kartu member kamu</p>

                <!-- Upload foto -->
                <div class="card" style="margin-top:18px;">
                    <div class="card-header"><h3>📷 Update Foto Profil</h3></div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                            <img id="previewImg" src="<?= ($photoPath&&file_exists($photoPath))?$photoPath:$avatarSvg ?>"
                                style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(232,56,13,0.4);">
                            <div style="flex:1;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Pilih Foto (JPG/PNG, max 2MB)</label>
                                    <input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Detail Member -->
            <div class="card">
                <div class="card-header"><h3>📋 Detail Member</h3></div>
                <div class="card-body">
                    <?php
                    $details = [
                        ['Member ID','🪪',$memberCode,'var(--accent)'],
                        ['Nama','👤',htmlspecialchars($member['name']),'#fff'],
                        ['Email','✉️',htmlspecialchars($member['email']),'rgba(255,255,255,0.7)'],
                        ['Telepon','📱',htmlspecialchars($member['phone']),'rgba(255,255,255,0.7)'],
                        ['Alamat','📍',htmlspecialchars($member['address']),'rgba(255,255,255,0.7)'],
                        ['Paket','📦',($member['package_name']??'-').($member['duration']?' ('.$member['duration'].' bln)':' (Harian)'),'rgba(255,255,255,0.85)'],
                        ['Status','✅',ucfirst($member['status']),$member['status']==='active'?'#4ADE80':'#F87171'],
                        ['Bergabung','📅',date('d M Y',strtotime($member['join_date'])),'rgba(255,255,255,0.7)'],
                        ['Expired','⏱️',$member['package_expiry']?date('d M Y',strtotime($member['package_expiry'])):'-',$isExpired?'#F87171':'rgba(255,255,255,0.7)'],
                    ];
                    foreach($details as $d): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:1.5px;text-transform:uppercase;"><?= $d[1] ?> <?= $d[0] ?></div>
                        <div style="font-size:13.5px;font-weight:700;color:<?= $d[3] ?>;text-align:right;max-width:60%;"><?= $d[2] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function previewPhoto(input) {
    if (input.files&&input.files[0]) {
        const r=new FileReader();
        r.onload=e=>{const img=document.getElementById('previewImg');img.src=e.target.result;};
        r.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once '../includes/footer.php'; ?>
