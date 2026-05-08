<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Query 1: GROUP BY with HAVING - members who paid > 500000
$memberPayments = $pdo->query("
    SELECT m.name, COUNT(p.payment_id) AS total_transactions, SUM(p.amount) AS total_paid
    FROM MEMBER m
    JOIN PAYMENT p ON m.member_id = p.member_id
    GROUP BY m.member_id, m.name
    HAVING SUM(p.amount) > 500000
    ORDER BY total_paid DESC
")->fetchAll();

// Query 2: INNER JOIN - all bookings with details
$bookingDetails = $pdo->query("
    SELECT b.booking_id, m.name AS member_name, s.date, s.time, t.name AS trainer_name, b.status
    FROM BOOKING b
    INNER JOIN MEMBER m ON b.member_id = m.member_id
    INNER JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
    INNER JOIN TRAINER t ON s.trainer_id = t.trainer_id
    ORDER BY s.date DESC
")->fetchAll();

// Query 3: SUBQUERY - members who have made payments
$membersWithPayments = $pdo->query("
    SELECT name, phone
    FROM MEMBER
    WHERE member_id IN (
        SELECT DISTINCT member_id FROM PAYMENT
    )
    ORDER BY name
")->fetchAll();

// Query 4: GROUP BY trainer schedule count
$trainerSchedules = $pdo->query("
    SELECT t.name AS trainer_name, t.specialization, COUNT(s.schedule_id) AS total_schedules
    FROM TRAINER t
    LEFT JOIN SCHEDULE s ON t.trainer_id = s.trainer_id
    GROUP BY t.trainer_id, t.name, t.specialization
    ORDER BY total_schedules DESC
")->fetchAll();
?>

<div class="main-content">
    <div class="topbar">
        <h1>📊 Reports & Queries</h1>
    </div>

    <div class="content">

        <!-- Report 1: GROUP BY + HAVING -->
        <div class="card">
            <div class="card-header">
                <h3>💰 Members with Total Payment > Rp 500.000</h3>
                <span style="font-size:12px; color:#888; background:#f0f2f5; padding:4px 10px; border-radius:6px;">GROUP BY + HAVING</span>
            </div>
            <div style="padding: 12px 24px; background:#f8f9fc; border-bottom:1px solid #eee;">
                <code style="font-size:12px; color:#555;">
                    SELECT m.name, COUNT(p.payment_id) AS total_transactions, SUM(p.amount) AS total_paid
                    FROM MEMBER m JOIN PAYMENT p ON m.member_id = p.member_id
                    GROUP BY m.member_id, m.name HAVING SUM(p.amount) > 500000
                </code>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Total Transactions</th>
                            <th>Total Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memberPayments as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= $r['total_transactions'] ?></td>
                            <td>Rp <?= number_format($r['total_paid'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($memberPayments)): ?>
                        <tr><td colspan="3" style="text-align:center; color:#aaa;">No data found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report 2: INNER JOIN -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Booking Details (Member + Schedule + Trainer)</h3>
                <span style="font-size:12px; color:#888; background:#f0f2f5; padding:4px 10px; border-radius:6px;">INNER JOIN</span>
            </div>
            <div style="padding: 12px 24px; background:#f8f9fc; border-bottom:1px solid #eee;">
                <code style="font-size:12px; color:#555;">
                    SELECT b.booking_id, m.name, s.date, s.time, t.name AS trainer_name, b.status
                    FROM BOOKING b INNER JOIN MEMBER m ON b.member_id = m.member_id
                    INNER JOIN SCHEDULE s ON b.schedule_id = s.schedule_id
                    INNER JOIN TRAINER t ON s.trainer_id = t.trainer_id
                </code>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Member</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookingDetails as $r): ?>
                        <tr>
                            <td><?= $r['booking_id'] ?></td>
                            <td><?= htmlspecialchars($r['member_name']) ?></td>
                            <td><?= $r['date'] ?></td>
                            <td><?= $r['time'] ?></td>
                            <td><?= htmlspecialchars($r['trainer_name']) ?></td>
                            <td><span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">

            <!-- Report 3: SUBQUERY -->
            <div class="card">
                <div class="card-header">
                    <h3>✅ Members Who Have Made Payments</h3>
                    <span style="font-size:12px; color:#888; background:#f0f2f5; padding:4px 10px; border-radius:6px;">SUBQUERY</span>
                </div>
                <div style="padding: 10px 24px; background:#f8f9fc; border-bottom:1px solid #eee;">
                    <code style="font-size:11px; color:#555;">
                        SELECT name, phone FROM MEMBER WHERE member_id IN (SELECT DISTINCT member_id FROM PAYMENT)
                    </code>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Member Name</th><th>Phone</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($membersWithPayments as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= htmlspecialchars($r['phone']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report 4: GROUP BY trainer -->
            <div class="card">
                <div class="card-header">
                    <h3>🏋️ Schedule Count per Trainer</h3>
                    <span style="font-size:12px; color:#888; background:#f0f2f5; padding:4px 10px; border-radius:6px;">GROUP BY</span>
                </div>
                <div style="padding: 10px 24px; background:#f8f9fc; border-bottom:1px solid #eee;">
                    <code style="font-size:11px; color:#555;">
                        SELECT t.name, COUNT(s.schedule_id) AS total FROM TRAINER t LEFT JOIN SCHEDULE s ON t.trainer_id = s.trainer_id GROUP BY t.trainer_id
                    </code>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr><th>Trainer</th><th>Specialization</th><th>Schedules</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainerSchedules as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['trainer_name']) ?></td>
                                <td><?= htmlspecialchars($r['specialization']) ?></td>
                                <td><?= $r['total_schedules'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
