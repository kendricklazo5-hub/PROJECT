<?php
require_once __DIR__ . '/helpers.php';
require_login();
$conn = db();

$userId = current_user()['id'];
$stmt = oci_parse($conn, "SELECT b.id, e.name, e.type, b.qty, b.status, TO_CHAR(b.borrowed_at, 'YYYY-MM-DD HH24:MI') AS borrowed_at, TO_CHAR(b.returned_at, 'YYYY-MM-DD HH24:MI') AS returned_at, b.feedback
                          FROM borrows b
                          JOIN equipment e ON e.id = b.equipment_id
                          WHERE b.user_id = :uid
                          ORDER BY b.borrowed_at DESC");
oci_bind_by_name($stmt, ':uid', $userId);
oci_execute($stmt);
$rows = [];
while ($row = oci_fetch_assoc($stmt)) {
    $rows[] = $row;
}

include __DIR__ . '/templates/header.php';
?>

<div class="card">
    <h2>My Borrowed Items</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Borrowed</th>
                <th>Returned</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6">No borrowed items yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['NAME']); ?> <span class="pill"><?php echo htmlspecialchars($item['TYPE']); ?></span></td>
                    <td><?php echo (int)$item['QTY']; ?></td>
                    <td><?php echo htmlspecialchars($item['STATUS']); ?></td>
                    <td><?php echo htmlspecialchars($item['BORROWED_AT']); ?></td>
                    <td><?php echo htmlspecialchars($item['RETURNED_AT']); ?></td>
                    <td>
                        <?php if ($item['STATUS'] === 'borrowed'): ?>
                            <form method="post" action="return_item.php">
                                <input type="hidden" name="borrow_id" value="<?php echo (int)$item['ID']; ?>">
                                <button type="submit" class="secondary">Return</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="submit_feedback.php">
                            <input type="hidden" name="borrow_id" value="<?php echo (int)$item['ID']; ?>">
                            <textarea name="feedback" rows="2" placeholder="Feedback" required><?php echo htmlspecialchars($item['FEEDBACK'] ?? ''); ?></textarea>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>

