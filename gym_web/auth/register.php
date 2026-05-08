<?php
session_start();
require_once '../includes/db.php';

$packages = $pdo->query("SELECT * FROM PACKAGE ORDER BY price ASC")->fetchAll();
$error = '';
$step = $_SESSION['reg_step'] ?? '1';

if (isset($_GET['back'])) { unset($_SESSION['reg'], $_SESSION['reg_step']); header("Location: register.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '1') {
    $name=$_POST['name']; $email=$_POST['email']; $phone=$_POST['phone'];
    $address=$_POST['address']; $password=$_POST['password']; $confirm=$_POST['confirm_password']; $pkg_id=$_POST['package_id'];
    if ($password !== $confirm) { $error='Password tidak cocok!'; $step='1'; }
    else {
        $check=$pdo->prepare("SELECT member_id FROM MEMBER WHERE email=?"); $check->execute([$email]);
        if ($check->fetch()) { $error='Email sudah terdaftar!'; $step='1'; }
        else { $_SESSION['reg']=compact('name','email','phone','address','password','pkg_id'); $_SESSION['reg_step']='2'; $step='2'; }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '2') {
    $reg = $_SESSION['reg'] ?? null;
    if ($reg) {
        $pay_method = $_POST['pay_method'];
        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== 0) { $error='Bukti pembayaran wajib diupload!'; $step='2'; }
        else {
            $allowed=['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($_FILES['proof']['type'],$allowed) || $_FILES['proof']['size']>5*1024*1024) { $error='File harus gambar dan < 5MB!'; $step='2'; }
            else {
                $ext=pathinfo($_FILES['proof']['name'],PATHINFO_EXTENSION);
                $proof='proof_'.time().'_'.rand(100,999).'.'.$ext;
                if (!is_dir('../uploads/proofs')) mkdir('../uploads/proofs', 0755, true);
                move_uploaded_file($_FILES['proof']['tmp_name'],'../uploads/proofs/'.$proof);
                $pkg=$pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?"); $pkg->execute([$reg['pkg_id']]); $pkg=$pkg->fetch();
                $expiry=$pkg['duration']>0?date('Y-m-d',strtotime("+{$pkg['duration']} months")):date('Y-m-d');
                $hashed=password_hash($reg['password'],PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO MEMBER (name,address,phone,email,password,package_id,status,join_date,package_expiry) VALUES(?,?,?,?,?,?,'inactive',CURDATE(),?)")->execute([$reg['name'],$reg['address'],$reg['phone'],$reg['email'],$hashed,$reg['pkg_id'],$expiry]);
                $mid=$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO PAYMENT (member_id,package_id,payment_date,amount,payment_method,proof_file,verified) VALUES(?,?,CURDATE(),?,?,?,0)")->execute([$mid,$reg['pkg_id'],$pkg['price'],$pay_method,$proof]);
                unset($_SESSION['reg'],$_SESSION['reg_step']); $step='success';
            }
        }
    }
}

if ($step==='2' && empty($_SESSION['reg'])) $step='1';
$regData=$_SESSION['reg']??[];
$selectedPkg=null;
if (!empty($regData['pkg_id'])) { $sp=$pdo->prepare("SELECT * FROM PACKAGE WHERE package_id=?"); $sp->execute([$regData['pkg_id']]); $selectedPkg=$sp->fetch(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar – GymPro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --accent:#E8380D;--accent-2:#FF5E2E;
  --black:#080808;--black-2:#101010;--black-3:#161616;--black-4:#1E1E1E;--black-5:#272727;
  --border:rgba(255,255,255,0.09);--border-hot:rgba(232,56,13,0.45);
  --w90:rgba(255,255,255,0.9);--w70:rgba(255,255,255,0.7);--w40:rgba(255,255,255,0.4);
  --w15:rgba(255,255,255,0.15);--w08:rgba(255,255,255,0.08);--w04:rgba(255,255,255,0.04);
}
html,body{min-height:100%;}
body{font-family:'Barlow',sans-serif;background:var(--black);color:#fff;display:flex;}

/* HERO side */
.hero{
  flex:1;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:flex-end;padding:52px;
  background:
    linear-gradient(to top,rgba(0,0,0,0.93) 0%,rgba(0,0,0,0.35) 60%,rgba(0,0,0,0.1) 100%),
    url('/uploads/gym1.jpg') center/cover no-repeat;
}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(232,56,13,0.15);border:1px solid rgba(232,56,13,0.4);border-radius:4px;padding:6px 14px;margin-bottom:18px;width:fit-content;font-size:10.5px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:var(--accent-2);}
.hero-badge span{width:6px;height:6px;border-radius:50%;background:var(--accent);display:block;animation:pulse 1.8s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.4;}}
.hero-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(52px,6.5vw,82px);line-height:0.93;letter-spacing:2px;color:#fff;text-shadow:0 4px 32px rgba(0,0,0,0.8);}
.hero-title em{color:var(--accent);font-style:normal;display:block;}
.hero-sub{margin-top:16px;font-size:14.5px;font-weight:500;color:var(--w70);max-width:380px;line-height:1.65;}

/* FORM panel */
.panel{width:500px;min-width:460px;background:var(--black-2);border-left:1px solid var(--border);display:flex;flex-direction:column;justify-content:center;padding:48px 42px;overflow-y:auto;min-height:100vh;}

.brand-mark{display:flex;align-items:center;gap:10px;margin-bottom:36px;}
.brand-icon{width:40px;height:40px;background:var(--accent);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 4px 14px rgba(232,56,13,0.4);}
.brand-text{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:2px;line-height:1;}
.brand-sub{font-size:9px;color:var(--w40);font-weight:700;letter-spacing:2.5px;text-transform:uppercase;margin-top:2px;}

.page-title{font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:900;text-transform:uppercase;letter-spacing:1px;line-height:1;}
.page-sub{font-size:13px;color:var(--w40);margin-top:5px;margin-bottom:24px;}

/* Step indicator */
.steps{display:flex;gap:5px;margin-bottom:26px;}
.sd{flex:1;height:3px;border-radius:3px;background:var(--black-5);}
.sd.done{background:var(--accent-2);}
.sd.act{background:var(--accent);}

/* Fields */
.fg{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.fg label{font-size:10px;font-weight:800;color:var(--w40);letter-spacing:2px;text-transform:uppercase;}
.fg input,.fg select,.fg textarea{
  padding:10px 13px;border:1.5px solid var(--border);border-radius:6px;
  font-size:13.5px;font-family:'Barlow',sans-serif;background:var(--black-4);color:#fff;
  transition:border-color .18s,box-shadow .18s;
}
.fg input::placeholder{color:var(--w40);}
.fg input:focus,.fg select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,56,13,0.18);}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}

/* Error */
.err{background:rgba(232,56,13,0.1);color:#FF7B5C;border:1px solid rgba(232,56,13,0.3);border-radius:6px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:16px;}

/* Buttons */
.btn{width:100%;padding:13px;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:13.5px;font-weight:900;cursor:pointer;font-family:'Barlow',sans-serif;text-transform:uppercase;letter-spacing:1.2px;transition:all .18s;margin-top:6px;box-shadow:0 4px 16px rgba(232,56,13,0.35);}
.btn:hover{background:var(--accent-2);box-shadow:0 6px 22px rgba(232,56,13,0.5);transform:translateY(-1px);}
.btn-back{background:var(--black-4);color:var(--w70);border:1px solid var(--border);display:block;text-align:center;padding:11px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:700;margin-top:10px;transition:all .18s;text-transform:uppercase;letter-spacing:0.5px;}
.btn-back:hover{background:var(--black-5);color:#fff;}

.lnk{text-align:center;margin-top:18px;font-size:13px;color:var(--w40);}
.lnk a{color:#fff;text-decoration:none;font-weight:700;border-bottom:1px solid rgba(232,56,13,0.5);}
.lnk a:hover{color:var(--accent);}

/* Package selector */
.pkgs{display:flex;flex-direction:column;gap:7px;margin-top:4px;}
.pr{display:none;}
.pl{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border:2px solid var(--border);border-radius:7px;cursor:pointer;transition:all .18s;background:var(--black-4);}
.pl:hover{border-color:var(--border-hot);}
.pr:checked+.pl{border-color:var(--accent);background:rgba(232,56,13,0.1);}
.pn{font-weight:700;font-size:13.5px;color:#fff;}
.pd{font-size:11.5px;color:var(--w40);margin-top:2px;}
.pp{font-weight:800;color:var(--accent);font-size:14.5px;}

/* Payment box */
.pay-box{background:var(--black-4);border:1px solid var(--border);border-radius:8px;padding:15px;margin-bottom:18px;}
.prow{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13.5px;color:var(--w70);}
.prow:last-child{border-bottom:none;font-weight:800;font-size:15px;color:var(--accent);}

/* Payment method */
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px;}
.mpr{display:none;}
.mpl{padding:11px 8px;border:2px solid var(--border);border-radius:7px;cursor:pointer;text-align:center;font-size:12px;font-weight:800;color:var(--w70);transition:all .18s;background:var(--black-4);text-transform:uppercase;letter-spacing:0.5px;}
.mpr:checked+.mpl{border-color:var(--accent);background:rgba(232,56,13,0.12);color:#fff;}

/* Payment detail box */
.dbox{display:none;background:var(--black-4);border:1.5px solid var(--border);border-radius:8px;padding:14px;margin:8px 0 12px;}
.dbox.show{display:block;}
.dbox h4{font-size:11.5px;font-weight:800;color:var(--w40);letter-spacing:2px;text-transform:uppercase;margin-bottom:10px;}
.dbox .anum{font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:900;color:#fff;letter-spacing:1px;}
.dbox .aname{font-size:12px;color:var(--w40);margin-top:2px;}
.qimg{width:150px;display:block;margin:8px auto;border-radius:8px;border:1px solid var(--border);}

/* Upload zone */
.pzone{border:2px dashed var(--border);border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:border-color .18s;position:relative;}
.pzone:hover{border-color:var(--accent);}
.pzone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.pzone .pzi{font-size:28px;margin-bottom:5px;}
.pzone p{font-size:12px;color:var(--w40);font-weight:500;}
#pprev{max-width:100%;border-radius:8px;margin-top:10px;display:none;}

/* Success */
.suc{text-align:center;padding:10px 0;}
.suc-i{font-size:60px;margin-bottom:14px;}
.suc-t{font-family:'Barlow Condensed',sans-serif;font-size:30px;font-weight:900;text-transform:uppercase;letter-spacing:1px;}
.suc-s{color:var(--w70);font-size:14px;margin:10px 0 24px;line-height:1.7;}

@media(max-width:900px){.hero{display:none;}.panel{width:100%;min-width:unset;border:none;background:linear-gradient(to bottom,rgba(0,0,0,0.82) 0%,rgba(0,0,0,0.82) 100%),url(/uploads/gym1.jpg) center/cover no-repeat;}}
</style>
</head>
<body>

<!-- HERO -->
<div class="hero">
  <div class="hero-badge"><span></span>Daftar Sekarang — Gratis</div>
  <h1 class="hero-title">START YOUR<em>JOURNEY.</em></h1>
  <p class="hero-sub">Langkah pertama menuju tubuh ideal dimulai dari sini. Pilih paket, bayar, dan mulai berlatih!</p>
</div>

<!-- PANEL -->
<div class="panel">
  <div class="brand-mark">
    <div class="brand-icon">💪</div>
    <div>
      <div class="brand-text">GYMPRO</div>
      <div class="brand-sub">Management System</div>
    </div>
  </div>

<?php if($step==='1'): ?>
  <div class="page-title">Daftar Member</div>
  <div class="page-sub">Lengkapi data diri dan pilih paket gym</div>
  <div class="steps"><div class="sd act"></div><div class="sd"></div></div>
  <?php if($error): ?><div class="err">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="POST" onsubmit="return chkPwd()">
    <input type="hidden" name="step" value="1">
    <div class="fg"><label>Nama Lengkap</label><input type="text" name="name" required placeholder="Nama lengkap kamu"></div>
    <div class="row2">
      <div class="fg"><label>Email</label><input type="email" name="email" required placeholder="email@contoh.com"></div>
      <div class="fg"><label>No. Telepon</label><input type="text" name="phone" required placeholder="08xxxxxxxxxx"></div>
    </div>
    <div class="fg"><label>Alamat</label><input type="text" name="address" required placeholder="Alamat lengkap"></div>
    <div class="row2">
      <div class="fg"><label>Password</label><input type="password" name="password" id="pwd" required placeholder="Min. 6 karakter" minlength="6"></div>
      <div class="fg"><label>Konfirmasi</label><input type="password" name="confirm_password" id="cpwd" required placeholder="Ulangi password"></div>
    </div>
    <div class="fg"><label>Pilih Paket Gym</label>
      <div class="pkgs">
        <?php foreach($packages as $p): ?>
        <input type="radio" name="package_id" id="pk<?=$p['package_id']?>" value="<?=$p['package_id']?>" class="pr" required>
        <label for="pk<?=$p['package_id']?>" class="pl">
          <div><div class="pn"><?=htmlspecialchars($p['package_name'])?></div><div class="pd"><?=$p['duration']==0?'Harian':$p['duration'].' Bulan'?></div></div>
          <div class="pp">Rp <?=number_format($p['price'],0,',','.')?></div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <button type="submit" class="btn">Lanjut ke Pembayaran →</button>
  </form>
  <div class="lnk">Sudah punya akun? <a href="login.php">Login</a></div>

<?php elseif($step==='2' && $selectedPkg): ?>
  <div class="page-title">Pembayaran</div>
  <div class="page-sub">Pilih metode dan upload bukti bayar</div>
  <div class="steps"><div class="sd done"></div><div class="sd act"></div></div>
  <?php if($error): ?><div class="err">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
  <div class="pay-box">
    <div class="prow"><span>Nama</span><span><?=htmlspecialchars($regData['name'])?></span></div>
    <div class="prow"><span>Paket</span><span><?=htmlspecialchars($selectedPkg['package_name'])?></span></div>
    <div class="prow"><span>Durasi</span><span><?=$selectedPkg['duration']==0?'Harian':$selectedPkg['duration'].' Bulan'?></span></div>
    <div class="prow"><span>Total</span><span>Rp <?=number_format($selectedPkg['price'],0,',','.')?></span></div>
  </div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="step" value="2">
    <div class="fg"><label>Metode Pembayaran</label></div>
    <div class="pay-grid">
      <input type="radio" name="pay_method" id="mq" value="QRIS" class="mpr" required onchange="sd('qris')"><label for="mq" class="mpl">📱 QRIS</label>
      <input type="radio" name="pay_method" id="mb" value="Transfer BNI" class="mpr" onchange="sd('bni')"><label for="mb" class="mpl">🏦 BNI</label>
      <input type="radio" name="pay_method" id="md" value="DANA" class="mpr" onchange="sd('dana')"><label for="md" class="mpl">💙 DANA</label>
      <input type="radio" name="pay_method" id="mg" value="GoPay" class="mpr" onchange="sd('gopay')"><label for="mg" class="mpl">💚 GoPay</label>
    </div>
    <div class="dbox" id="d_qris"><h4>Scan QRIS</h4><img src="../uploads/qris.jpeg" class="qimg" alt="QRIS"><p style="text-align:center;font-size:11.5px;margin-top:6px;color:var(--w40);">Scan dengan e-wallet atau m-banking apapun</p></div>
    <div class="dbox" id="d_bni"><h4>Transfer BNI</h4><div class="anum">1924182745</div><div class="aname">a.n. GYM PRO</div><div style="margin-top:8px;font-size:12px;color:var(--w40);">Nominal: <strong style="color:var(--accent);">Rp <?=number_format($selectedPkg['price'],0,',','.')?></strong></div></div>
    <div class="dbox" id="d_dana"><h4>Transfer DANA</h4><div class="anum">082386210045</div><div class="aname">a.n. GYM PRO</div><div style="margin-top:8px;font-size:12px;color:var(--w40);">Nominal: <strong style="color:var(--accent);">Rp <?=number_format($selectedPkg['price'],0,',','.')?></strong></div></div>
    <div class="dbox" id="d_gopay"><h4>Transfer GoPay</h4><div class="anum">082386210045</div><div class="aname">a.n. GYM PRO</div><div style="margin-top:8px;font-size:12px;color:var(--w40);">Nominal: <strong style="color:var(--accent);">Rp <?=number_format($selectedPkg['price'],0,',','.')?></strong></div></div>
    <div class="fg" style="margin-top:4px;"><label>Upload Bukti Pembayaran</label></div>
    <div class="pzone"><input type="file" name="proof" accept="image/*" onchange="prvw(this)" required><div class="pzi">📎</div><p>Klik untuk upload screenshot / bukti transfer<br><span style="font-size:11px;">JPG, PNG — maks 5MB</span></p><img id="pprev" src="" alt="Preview"></div>
    <button type="submit" class="btn" style="margin-top:14px;">✅ Konfirmasi Pembayaran</button>
  </form>
  <a href="register.php?back=1" class="btn-back">← Kembali</a>

<?php elseif($step==='success'): ?>
  <div class="suc">
    <div class="suc-i">🎉</div>
    <div class="suc-t">Berhasil!</div>
    <div class="suc-s">Bukti pembayaran sudah diterima.<br>Akun kamu akan diaktifkan setelah admin memverifikasi.</div>
    <a href="login.php" class="btn" style="display:block;text-decoration:none;text-align:center;">Masuk Sekarang →</a>
  </div>
<?php endif; ?>
</div>

<script>
function chkPwd(){if(document.getElementById('pwd').value!==document.getElementById('cpwd').value){alert('Password tidak cocok!');return false;}return true;}
function sd(m){['qris','bni','dana','gopay'].forEach(x=>document.getElementById('d_'+x)?.classList.remove('show'));document.getElementById('d_'+m)?.classList.add('show');}
function prvw(i){if(i.files&&i.files[0]){const r=new FileReader();r.onload=e=>{const img=document.getElementById('pprev');img.src=e.target.result;img.style.display='block';};r.readAsDataURL(i.files[0]);}}
</script>
</body>
</html>