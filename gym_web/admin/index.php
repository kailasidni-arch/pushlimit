<?php
require_once '../includes/db.php';
require_once 'header.php';

$totalMembers  = $pdo->query("SELECT COUNT(*) FROM MEMBER")->fetchColumn();
$totalTrainers = $pdo->query("SELECT COUNT(*) FROM TRAINER")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM BOOKING")->fetchColumn();
$totalPayments = $pdo->query("SELECT SUM(amount) FROM PAYMENT WHERE verified=1")->fetchColumn() ?? 0;
$totalPackages = $pdo->query("SELECT COUNT(*) FROM PACKAGE")->fetchColumn();
$pendingPayments = $pdo->query("SELECT COUNT(*) FROM PAYMENT WHERE verified=0")->fetchColumn();

$recentBookings = $pdo->query("
    SELECT b.booking_id, m.name AS member_name, s.date, s.time, t.name AS trainer_name, b.status
    FROM BOOKING b
    INNER JOIN MEMBER m ON b.member_id = m.member_id
    INNER JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    INNER JOIN TRAINER t ON s.trainer_id = t.trainer_id
    ORDER BY b.booking_id DESC LIMIT 5
")->fetchAll();

$topMembers = $pdo->query("
    SELECT m.name, COUNT(p.payment_id) AS total_transactions, SUM(p.amount) AS total_paid
    FROM MEMBER m
    JOIN PAYMENT p ON m.member_id = p.member_id
    GROUP BY m.member_id, m.name
    HAVING SUM(p.amount) > 0
    ORDER BY total_paid DESC LIMIT 5
")->fetchAll();
?>

<div class="main-content">
    <!-- Hero topbar -->
    <div style="background:linear-gradient(135deg,#161616 0%,#1a0f0a 100%);border-bottom:1px solid rgba(255,255,255,0.09);padding:28px 28px 26px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;background:radial-gradient(circle,rgba(232,56,13,0.12) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--accent),transparent);opacity:0.4;"></div>
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:10.5px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:rgba(232,56,13,0.9);margin-bottom:6px;">ADMIN PANEL</div>
                <h1 style="font-family:'Bebas Neue',sans-serif;font-size:38px;letter-spacing:2px;line-height:1;color:#fff;">DASHBOARD <span style="color:var(--accent);">OVERVIEW</span></h1>
                <div style="font-size:13px;color:rgba(255,255,255,0.4);margin-top:6px;font-weight:500;"><?= date('l, d F Y') ?></div>
            </div>
            <?php if($pendingPayments > 0): ?>
            <a href="payments.php" style="text-decoration:none;">
                <div style="background:rgba(232,56,13,0.15);border:1px solid rgba(232,56,13,0.4);border-radius:8px;padding:12px 18px;display:flex;align-items:center;gap:10px;transition:all .18s;" onmouseover="this.style.background='rgba(232,56,13,0.25)'" onmouseout="this.style.background='rgba(232,56,13,0.15)'">
                    <div style="font-size:22px;">🔔</div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:var(--accent-2);text-transform:uppercase;letter-spacing:1px;"><?= $pendingPayments ?> Pembayaran</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:2px;">Menunggu verifikasi</div>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info"><h3><?= $totalMembers ?></h3><p>Total Members</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">🏋️</div>
                <div class="stat-info"><h3><?= $totalTrainers ?></h3><p>Total Trainers</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">📦</div>
                <div class="stat-info"><h3><?= $totalPackages ?></h3><p>Packages</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📋</div>
                <div class="stat-info"><h3><?= $totalBookings ?></h3><p>Total Bookings</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">💳</div>
                <div class="stat-info"><h3>Rp <?= number_format($totalPayments,0,',','.') ?></h3><p>Total Revenue</p></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header">
                    <h3>📋 Recent Bookings</h3>
                    <a href="bookings.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Member</th><th>Trainer</th><th>Tanggal</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentBookings)): ?>
                            <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">📋</div><p>Belum ada booking</p></div></td></tr>
                            <?php else: ?>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($b['member_name']) ?></td>
                                <td style="color:rgba(255,255,255,0.6);"><?= htmlspecialchars($b['trainer_name']) ?></td>
                                <td style="color:rgba(255,255,255,0.6);"><?= $b['date'] ?></td>
                                <td><span class="status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Members -->
            <div class="card">
                <div class="card-header">
                    <h3>🏆 Top Members by Revenue</h3>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Member</th><th>Transaksi</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php if(empty($topMembers)): ?>
                            <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">💰</div><p>Belum ada data</p></div></td></tr>
                            <?php else: ?>
                            <?php foreach ($topMembers as $i => $m): ?>
                            <tr>
                                <td style="font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:900;color:<?= $i===0?'#F5A623':($i===1?'rgba(255,255,255,0.5)':($i===2?'#CD7F32':'rgba(255,255,255,0.3)')) ?>;"><?= $i+1 ?></td>
                                <td style="font-weight:700;"><?= htmlspecialchars($m['name']) ?></td>
                                <td style="color:rgba(255,255,255,0.5);"><?= $m['total_transactions'] ?>x</td>
                                <td style="font-weight:700;color:var(--accent-2);">Rp <?= number_format($m['total_paid'],0,',','.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-top:0;">
            <div class="card-header"><h3>⚡ Akses Cepat</h3></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;">
                    <?php
                    $actions = [
                        ['members.php','👥','Members','rgba(59,130,246,0.12)','rgba(59,130,246,0.3)'],
                        ['trainers.php','🏋️','Trainers','rgba(34,197,94,0.10)','rgba(34,197,94,0.3)'],
                        ['payments.php','💳','Payments','rgba(232,56,13,0.12)','rgba(232,56,13,0.35)'],
                        ['schedules.php','📅','Schedules','rgba(168,85,247,0.10)','rgba(168,85,247,0.3)'],
                        ['packages.php','📦','Packages','rgba(245,166,35,0.10)','rgba(245,166,35,0.3)'],
                        ['reports.php','📊','Reports','rgba(6,182,212,0.10)','rgba(6,182,212,0.3)'],
                    ];
                    foreach($actions as $a): ?>
                    <a href="<?= $a[0] ?>" style="text-decoration:none;">
                        <div style="background:<?= $a[3] ?>;border:1px solid <?= $a[4] ?>;border-radius:10px;padding:18px;text-align:center;transition:all .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='';this.style.boxShadow='';">
                            <div style="font-size:26px;margin-bottom:7px;"><?= $a[1] ?></div>
                            <div style="font-size:11.5px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:0.8px;"><?= $a[2] ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
