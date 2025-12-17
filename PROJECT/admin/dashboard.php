<?php
require_once __DIR__ . '/../helpers.php';
require_admin();
$conn = db();

$summarySql = "SELECT 
    (SELECT COUNT(*) FROM equipment) AS total_equipment,
    (SELECT NVL(SUM(total_qty),0) FROM equipment) AS total_qty,
    (SELECT NVL(SUM(available_qty),0) FROM equipment) AS available_qty,
    (SELECT COUNT(*) FROM borrows WHERE status = 'borrowed') AS active_borrows,
    (SELECT COUNT(*) FROM borrows) AS total_borrows
    FROM dual";
$stmt = oci_parse($conn, $summarySql);
oci_execute($stmt);
$summary = oci_fetch_assoc($stmt);

$recent = oci_parse($conn, "SELECT b.id, u.username, e.name, b.qty, b.status, TO_CHAR(b.borrowed_at, 'YYYY-MM-DD HH24:MI') AS borrowed_at
                            FROM borrows b
                            JOIN users u ON u.id = b.user_id
                            JOIN equipment e ON e.id = b.equipment_id
                            ORDER BY b.borrowed_at DESC FETCH FIRST 10 ROWS ONLY");
oci_execute($recent);
$recentRows = [];
while ($row = oci_fetch_assoc($recent)) {
    $recentRows[] = $row;
}

include __DIR__ . '/../templates/header.php';
?>

<div class="card grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
    <div><h3>Total Equipment</h3><p class="pill"><?php echo (int)$summary['TOTAL_EQUIPMENT']; ?></p></div>
    <div><h3>Total Qty</h3><p class="pill"><?php echo (int)$summary['TOTAL_QTY']; ?></p></div>
    <div><h3>Available Qty</h3><p class="pill"><?php echo (int)$summary['AVAILABLE_QTY']; ?></p></div>
    <div><h3>Active Borrows</h3><p class="pill"><?php echo (int)$summary['ACTIVE_BORROWS']; ?></p></div>
    <div><h3>Borrow History</h3><p class="pill"><?php echo (int)$summary['TOTAL_BORROWS']; ?></p></div>
</div>

<div class="card">
    <h2>Recent Borrow Activity</h2>
    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Equipment</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Borrowed At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentRows)): ?>
                <tr><td colspan="5">No activity yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($recentRows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['USERNAME']); ?></td>
                    <td><?php echo htmlspecialchars($r['NAME']); ?></td>
                    <td><?php echo (int)$r['QTY']; ?></td>
                    <td><?php echo htmlspecialchars($r['STATUS']); ?></td>
                    <td><?php echo htmlspecialchars($r['BORROWED_AT']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>

