<?php
// This file outputs payment detail boxes for register/extend pages
// Requires $cfg (settings array) and $amount (int) to be set before including
?>
<div id="d_qris" style="display:none;background:var(--bg,#FDF8F3);border:1.5px solid var(--bp,#E8D5B7);border-radius:10px;padding:15px;margin-bottom:14px;">
    <div style="font-size:12.5px;font-weight:700;color:#5C4033;margin-bottom:8px;">📱 Scan QRIS</div>
    <img src="<?= isset($base_path) ? $base_path : '../' ?>uploads/<?= $cfg['qris_file'] ?>" style="width:160px;display:block;margin:0 auto;border-radius:8px;border:1px solid #EDE0D0;" alt="QRIS">
    <p style="text-align:center;font-size:11.5px;color:#9C7E6A;margin-top:6px;">Scan dengan aplikasi e-wallet atau mobile banking apapun</p>
</div>
<div id="d_bni" style="display:none;background:var(--bg,#FDF8F3);border:1.5px solid var(--bp,#E8D5B7);border-radius:10px;padding:15px;margin-bottom:14px;">
    <div style="font-size:12.5px;font-weight:700;color:#5C4033;margin-bottom:6px;">🏦 Transfer <?= htmlspecialchars($cfg['bank_name']) ?></div>
    <div style="font-size:16px;font-weight:700;color:#2C1A0E;"><?= htmlspecialchars($cfg['bank_account']) ?></div>
    <div style="font-size:12px;color:#9C7E6A;">a.n. <?= htmlspecialchars($cfg['bank_holder']) ?></div>
    <div style="margin-top:8px;font-size:12px;color:#9C7E6A;">Nominal: <strong style="color:#6B4F35;">Rp <?= number_format($amount,0,',','.') ?></strong></div>
</div>
<div id="d_dana" style="display:none;background:var(--bg,#FDF8F3);border:1.5px solid var(--bp,#E8D5B7);border-radius:10px;padding:15px;margin-bottom:14px;">
    <div style="font-size:12.5px;font-weight:700;color:#5C4033;margin-bottom:6px;">💙 DANA</div>
    <div style="font-size:16px;font-weight:700;color:#2C1A0E;"><?= htmlspecialchars($cfg['dana_number']) ?></div>
    <div style="font-size:12px;color:#9C7E6A;">a.n. <?= htmlspecialchars($cfg['dana_holder']) ?></div>
    <div style="margin-top:8px;font-size:12px;color:#9C7E6A;">Nominal: <strong style="color:#6B4F35;">Rp <?= number_format($amount,0,',','.') ?></strong></div>
</div>
<div id="d_gopay" style="display:none;background:var(--bg,#FDF8F3);border:1.5px solid var(--bp,#E8D5B7);border-radius:10px;padding:15px;margin-bottom:14px;">
    <div style="font-size:12.5px;font-weight:700;color:#5C4033;margin-bottom:6px;">💚 GoPay</div>
    <div style="font-size:16px;font-weight:700;color:#2C1A0E;"><?= htmlspecialchars($cfg['gopay_number']) ?></div>
    <div style="font-size:12px;color:#9C7E6A;">a.n. <?= htmlspecialchars($cfg['gopay_holder']) ?></div>
    <div style="margin-top:8px;font-size:12px;color:#9C7E6A;">Nominal: <strong style="color:#6B4F35;">Rp <?= number_format($amount,0,',','.') ?></strong></div>
</div>
