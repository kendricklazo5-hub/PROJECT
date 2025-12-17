<?php
require_once __DIR__ . '/helpers.php';
$conn = db();

$search = sanitize($_GET['search'] ?? '');
$type = sanitize($_GET['type'] ?? '');

$query = "SELECT id, name, type, description, total_qty, available_qty FROM equipment WHERE 1=1";
$binds = [];
if ($search !== '') {
    $query .= " AND LOWER(name) LIKE :search";
    $binds[':search'] = '%' . strtolower($search) . '%';
}
if ($type !== '') {
    $query .= " AND LOWER(type) = :type";
    $binds[':type'] = strtolower($type);
}
$stmt = oci_parse($conn, $query);
foreach ($binds as $key => $val) {
    oci_bind_by_name($stmt, $key, $binds[$key]);
}
oci_execute($stmt);
$equipment = [];
while ($row = oci_fetch_assoc($stmt)) {
    $equipment[] = $row;
}

include __DIR__ . '/templates/header.php';
?>

<div class="card">
    <h2>Equipment Catalog</h2>
    <form method="get" class="grid" style="grid-template-columns: 1fr 1fr 120px;">
        <div class="field">
            <label for="search">Search by name</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="field">
            <label for="type">Filter by type</label>
            <input type="text" id="type" name="type" value="<?php echo htmlspecialchars($type); ?>" placeholder="e.g. weights, mats">
        </div>
        <div class="field">
            <label>&nbsp;</label>
            <button type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="grid">
    <?php if (empty($equipment)): ?>
        <p>No equipment found.</p>
    <?php endif; ?>
    <?php foreach ($equipment as $item): ?>
        <div class="equipment-card">
            <h3><?php echo htmlspecialchars($item['NAME']); ?></h3>
            <p class="pill"><?php echo htmlspecialchars($item['TYPE'] ?: 'General'); ?></p>
            <p><?php echo htmlspecialchars($item['DESCRIPTION'] ?: ''); ?></p>
            <p>Total: <?php echo (int)$item['TOTAL_QTY']; ?> | Available: <?php echo (int)$item['AVAILABLE_QTY']; ?></p>
            <?php if (current_user()): ?>
                <form method="post" action="borrow.php">
                    <input type="hidden" name="equipment_id" value="<?php echo (int)$item['ID']; ?>">
                    <div class="field">
                        <label>Quantity to borrow</label>
                        <input type="number" name="qty" min="1" max="<?php echo (int)$item['AVAILABLE_QTY']; ?>" required>
                    </div>
                    <button type="submit" <?php echo ((int)$item['AVAILABLE_QTY'] <= 0) ? 'disabled' : ''; ?>>
                        <?php echo ((int)$item['AVAILABLE_QTY'] <= 0) ? 'Unavailable' : 'Borrow'; ?>
                    </button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Log in</a> to borrow</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>

