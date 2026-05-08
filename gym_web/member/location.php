<?php
require_once '../includes/db.php';
require_once 'header.php';

// Load settings dynamically
function gs($pdo, $key, $default='') {
    $s=$pdo->prepare("SELECT setting_value FROM GYM_SETTINGS WHERE setting_key=?");
    try { $s->execute([$key]); $r=$s->fetch(); return $r?$r['setting_value']:$default; }
    catch(Exception $e) { return $default; }
}

$gym_name    = gs($pdo,'gym_name','GymPro');
$gym_address = gs($pdo,'gym_address','Jl. Sudirman No. 88, Jakarta');
$gym_phone   = gs($pdo,'gym_phone','(021) 5555-1234');
$gym_email   = gs($pdo,'gym_email','info@gympro.id');
$gym_lat     = gs($pdo,'gym_lat','-6.2175');
$gym_lng     = gs($pdo,'gym_lng','106.8050');

// Build OSM bbox from lat/lng
$lat = (float)$gym_lat; $lng = (float)$gym_lng;
$bbox = ($lng-0.015).'%2C'.($lat-0.012).'%2C'.($lng+0.015).'%2C'.($lat+0.012);
?>

<div class="main-content">
    <div class="topbar"><h1>📍 Lokasi Gym</h1></div>
    <div class="content">
        <div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">

            <div class="card">
                <div class="card-header"><h3>🗺️ Peta Lokasi</h3></div>
                <div style="height:460px; border-radius:0 0 12px 12px; overflow:hidden;">
                    <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?=$bbox?>&layer=mapnik&marker=<?=$gym_lat?>%2C<?=$gym_lng?>"
                        style="width:100%;height:100%;border:none;" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:18px;">
                <div class="card">
                    <div class="card-header"><h3>🏢 <?= htmlspecialchars($gym_name) ?></h3></div>
                    <div class="card-body">
                        <div style="display:flex;flex-direction:column;gap:14px;">
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <span style="font-size:18px;">📍</span>
                                <div><div style="font-weight:600;font-size:13px;margin-bottom:2px;">Alamat</div>
                                <div style="color:var(--text-light);font-size:13px;line-height:1.5;"><?= nl2br(htmlspecialchars($gym_address)) ?></div></div>
                            </div>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <span style="font-size:18px;">📞</span>
                                <div><div style="font-weight:600;font-size:13px;margin-bottom:2px;">Telepon</div>
                                <div style="color:var(--text-light);font-size:13px;"><?= htmlspecialchars($gym_phone) ?></div></div>
                            </div>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <span style="font-size:18px;">📧</span>
                                <div><div style="font-weight:600;font-size:13px;margin-bottom:2px;">Email</div>
                                <div style="color:var(--text-light);font-size:13px;"><?= htmlspecialchars($gym_email) ?></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3>⏰ Jam Operasional</h3></div>
                    <div class="card-body">
                        <?php $hours=['Senin – Jumat'=>'05:00 – 22:00','Sabtu'=>'06:00 – 21:00','Minggu'=>'07:00 – 20:00','Hari Libur'=>'08:00 – 18:00'];
                        $today=date('N');
                        foreach($hours as $day=>$time):
                            $isToday=($today<=5&&$day==='Senin – Jumat')||($today==6&&$day==='Sabtu')||($today==7&&$day==='Minggu'); ?>
                        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;<?=$isToday?'color:var(--brown-dark);font-weight:700;':''?>">
                            <span><?=$day?><?=$isToday?' (Hari Ini)':''?></span><span><?=$time?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="https://www.google.com/maps/search/?api=1&query=<?=$gym_lat?>,<?=$gym_lng?>" target="_blank" class="btn btn-primary" style="text-align:center;text-decoration:none;">🗺️ Buka di Google Maps</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
