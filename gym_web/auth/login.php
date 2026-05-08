<?php
session_start();
require_once '../includes/db.php';
if (isset($_SESSION['user_id'])) {
    $r=$_SESSION['role'];
    header("Location: ../".($r==='admin'?'admin':($r==='trainer'?'trainer':'member'))."/index.php"); exit;
}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']); $password=$_POST['password']; $role=$_POST['role'];
    if ($role==='admin') $stmt=$pdo->prepare("SELECT * FROM ADMIN WHERE email=?");
    elseif ($role==='trainer') $stmt=$pdo->prepare("SELECT * FROM TRAINER WHERE email=?");
    else $stmt=$pdo->prepare("SELECT * FROM MEMBER WHERE email=?");
    $stmt->execute([$email]); $user=$stmt->fetch();
    if ($user && password_verify($password,$user['password'])) {
        if ($role==='member' && $user['status']==='inactive') { $error='Akun belum aktif. Tunggu verifikasi admin.'; }
        else {
            $_SESSION['user_id']=$role==='admin'?$user['admin_id']:($role==='trainer'?$user['trainer_id']:$user['member_id']);
            $_SESSION['name']=$user['name']; $_SESSION['email']=$user['email']; $_SESSION['role']=$role;
            header("Location: ../".($role==='admin'?'admin':($role==='trainer'?'trainer':'member'))."/index.php"); exit;
        }
    } else { $error='Email atau password salah!'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – GymPro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --accent:#E8380D; --accent-2:#FF5E2E;
  --black:#080808; --black-2:#101010; --black-3:#161616; --black-4:#1E1E1E;
  --border:rgba(255,255,255,0.09); --border-hot:rgba(232,56,13,0.45);
  --w90:rgba(255,255,255,0.9); --w70:rgba(255,255,255,0.7); --w40:rgba(255,255,255,0.4); --w15:rgba(255,255,255,0.15); --w08:rgba(255,255,255,0.08); --w04:rgba(255,255,255,0.04);
}
html,body{height:100%;}
body{font-family:'Barlow',sans-serif;background:var(--black);color:#fff;display:flex;min-height:100vh;}

/* LEFT PANEL — gym photo */
.hero{
  flex:1; position:relative; overflow:hidden; display:flex; flex-direction:column; justify-content:flex-end; padding:52px;
  background:
    linear-gradient(135deg, rgba(232,56,13,0.08) 0%, transparent 50%),
    linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.15) 100%),
    url('/uploads/gym1.jpg') center/cover no-repeat;
}

.hero-noise{
  position:absolute;inset:0;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
  opacity:0.4;
}

.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(232,56,13,0.15);border:1px solid rgba(232,56,13,0.4);
  border-radius:4px;padding:6px 14px;margin-bottom:20px;width:fit-content;
  font-size:11px;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:var(--accent-2);
}
.hero-badge span{width:6px;height:6px;border-radius:50%;background:var(--accent);display:block;animation:pulse 1.8s infinite;}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.5;transform:scale(0.8);}}

.hero-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(54px, 7vw, 88px);
  line-height:0.92; letter-spacing:2px;
  color:#fff;
  text-shadow:0 4px 32px rgba(0,0,0,0.8);
}
.hero-title em{color:var(--accent);font-style:normal;display:block;}

.hero-sub{
  margin-top:18px;font-size:15px;font-weight:500;color:var(--w70);
  max-width:400px;line-height:1.65;
}

.hero-stats{
  display:flex;gap:32px;margin-top:30px;
  padding-top:26px;border-top:1px solid rgba(255,255,255,0.12);
}
.hs-item .hs-num{
  font-family:'Barlow Condensed',sans-serif;
  font-size:30px;font-weight:900;color:#fff;letter-spacing:1px;
}
.hs-item .hs-num span{color:var(--accent);}
.hs-item .hs-lbl{font-size:11px;color:var(--w40);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-top:2px;}

/* RIGHT PANEL — login form */
.panel{
  width:460px;min-width:420px;
  background:var(--black-2);
  border-left:1px solid var(--border);
  display:flex;flex-direction:column;justify-content:center;
  padding:52px 44px;overflow-y:auto;
}

.brand-mark{
  display:flex;align-items:center;gap:10px;margin-bottom:44px;
}
.brand-mark-icon{
  width:42px;height:42px;background:var(--accent);border-radius:8px;
  display:flex;align-items:center;justify-content:center;font-size:20px;
  flex-shrink:0;box-shadow:0 4px 16px rgba(232,56,13,0.4);
}
.brand-mark-text{
  font-family:'Bebas Neue',sans-serif;font-size:26px;letter-spacing:2px;color:#fff;line-height:1;
}
.brand-mark-sub{font-size:9.5px;color:var(--w40);font-weight:700;letter-spacing:2.5px;text-transform:uppercase;margin-top:2px;}

.login-heading{font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:1px;line-height:1;}
.login-sub{font-size:13.5px;color:var(--w40);margin-top:6px;margin-bottom:30px;font-weight:500;}

/* Role tabs */
.tabs{display:flex;gap:4px;background:var(--black-3);border-radius:8px;padding:4px;border:1px solid var(--border);margin-bottom:24px;}
.tab-btn{
  flex:1;padding:9px 4px;border:none;background:transparent;
  border-radius:6px;font-size:12px;font-weight:800;cursor:pointer;
  color:var(--w40);transition:all 0.18s;font-family:'Barlow',sans-serif;
  letter-spacing:0.5px;text-transform:uppercase;
}
.tab-btn.active{background:var(--accent);color:#fff;box-shadow:0 2px 10px rgba(232,56,13,0.4);}

/* Fields */
.field{margin-bottom:18px;}
.field label{display:block;font-size:10.5px;font-weight:800;color:var(--w40);letter-spacing:2px;text-transform:uppercase;margin-bottom:7px;}
.field input{
  width:100%;padding:12px 14px;
  border:1.5px solid var(--border);border-radius:6px;
  font-size:14px;font-family:'Barlow',sans-serif;
  background:var(--black-4);color:#fff;
  transition:border-color 0.18s,box-shadow 0.18s;
}
.field input::placeholder{color:var(--w40);}
.field input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(232,56,13,0.20);}

.err-box{
  background:rgba(232,56,13,0.10);color:#FF7B5C;
  border:1px solid rgba(232,56,13,0.3);
  border-radius:6px;padding:10px 14px;
  font-size:13px;font-weight:600;margin-bottom:18px;
}

.submit-btn{
  width:100%;padding:14px;
  background:var(--accent);color:#fff;border:none;border-radius:6px;
  font-size:14px;font-weight:900;font-family:'Barlow',sans-serif;
  text-transform:uppercase;letter-spacing:1.5px;cursor:pointer;
  transition:all 0.18s;box-shadow:0 4px 18px rgba(232,56,13,0.35);
  margin-top:4px;
}
.submit-btn:hover{background:var(--accent-2);box-shadow:0 6px 26px rgba(232,56,13,0.55);transform:translateY(-1px);}

.register-link{
  text-align:center;margin-top:22px;font-size:13px;color:var(--w40);
}
.register-link a{color:#fff;text-decoration:none;font-weight:700;border-bottom:1px solid rgba(232,56,13,0.5);}
.register-link a:hover{color:var(--accent);}

@media(max-width:900px){
  .hero{display:none;}
  .panel{width:100%;min-width:unset;border:none;background:linear-gradient(to bottom,rgba(0,0,0,0.82) 0%,rgba(0,0,0,0.82) 100%),url(/uploads/gym1.jpg) center/cover no-repeat;}
}
</style>
</head>
<body>

<!-- LEFT: Hero -->
<div class="hero">
  <div class="hero-noise"></div>
  <div class="hero-badge"><span></span>GymPro — Premium Fitness</div>
  <h1 class="hero-title">PUSH YOUR<em>LIMITS.</em></h1>
  <p class="hero-sub">Bergabung bersama ribuan member yang sudah membuktikan hasil nyata. Mulai perjalananmu hari ini.</p>
  <div class="hero-stats">
    <div class="hs-item"><div class="hs-num">500<span>+</span></div><div class="hs-lbl">Member Aktif</div></div>
    <div class="hs-item"><div class="hs-num">30<span>+</span></div><div class="hs-lbl">Trainer Pro</div></div>
    <div class="hs-item"><div class="hs-num">24<span>/7</span></div><div class="hs-lbl">Open Access</div></div>
  </div>
</div>

<!-- RIGHT: Form -->
<div class="panel">
  <div class="brand-mark">
    <div class="brand-mark-icon">💪</div>
    <div>
      <div class="brand-mark-text">GYMPRO</div>
      <div class="brand-mark-sub">Management System</div>
    </div>
  </div>

  <div class="login-heading">Selamat Datang</div>
  <div class="login-sub">Masuk ke akun kamu untuk melanjutkan</div>

  <!-- Role Tabs -->
  <div class="tabs">
    <button type="button" class="tab-btn active" onclick="setRole('member',this)">👥 Member</button>
    <button type="button" class="tab-btn" onclick="setRole('trainer',this)">🏋️ Trainer</button>
    <button type="button" class="tab-btn" onclick="setRole('admin',this)">⚙️ Admin</button>
  </div>

  <?php if($error): ?>
  <div class="err-box">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="role" id="roleInput" value="member">
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" required placeholder="Masukkan email kamu" value="<?= htmlspecialchars($_POST['email']??'') ?>">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required placeholder="Masukkan password">
    </div>
    <button type="submit" class="submit-btn">Masuk Sekarang →</button>
  </form>

  <div class="register-link">
    Belum punya akun? <a href="register.php">Daftar sebagai Member</a>
  </div>
</div>

<script>
function setRole(r, el) {
  document.getElementById('roleInput').value = r;
  document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
</script>
</body>
</html>
