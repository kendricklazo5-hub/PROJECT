<?php
require_once __DIR__ . '/../helpers.php';
require_admin();
$conn = db();
$errors = [];

if (is_post()) {
    $action = $_POST['action'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    $type = sanitize($_POST['type'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $total = (int)($_POST['total_qty'] ?? 0);
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        if ($name === '' || $total <= 0) {
            $errors[] = 'Name and total quantity are required.';
        } else {
            $stmt = oci_parse($conn, "INSERT INTO equipment (name, type, description, total_qty, available_qty) VALUES (:n, :t, :d, :tot, :tot)");
            oci_bind_by_name($stmt, ':n', $name);
            oci_bind_by_name($stmt, ':t', $type);
            oci_bind_by_name($stmt, ':d', $desc);
            oci_bind_by_name($stmt, ':tot', $total);
            oci_execute($stmt);
            flash('success', 'Equipment added.');
            header('Location: equipment.php');
            exit;
        }
    }

    if ($action === 'update' && $id > 0) {
        if ($name === '' || $total <= 0) {
            $errors[] = 'Name and total quantity are required.';
        } else {
            $stmt = oci_parse($conn, "UPDATE equipment SET name = :n, type = :t, description = :d, total_qty = :tot, available_qty = :avail WHERE id = :id");
            $available = max(0, $total - (int)($_POST['borrowed_qty'] ?? 0));
            oci_bind_by_name($stmt, ':n', $name);
            oci_bind_by_name($stmt, ':t', $type);
            oci_bind_by_name($stmt, ':d', $desc);
            oci_bind_by_name($stmt, ':tot', $total);
            oci_bind_by_name($stmt, ':avail', $available);
            oci_bind_by_name($stmt, ':id', $id);
            oci_execute($stmt);
            flash('success', 'Equipment updated.');
            header('Location: equipment.php');
            exit;
        }
    }

    if ($action === 'delete' && $id > 0) {
        $del = oci_parse($conn, "DELETE FROM equipment WHERE id = :id");
        oci_bind_by_name($del, ':id', $id);
        oci_execute($del);
        flash('success', 'Equipment removed.');
        header('Location: equipment.php');
        exit;
    }
}

$stmt = oci_parse($conn, "SELECT id, name, type, description, total_qty, available_qty, (total_qty - available_qty) AS borrowed_qty FROM equipment ORDER BY name");
oci_execute($stmt);
$items = [];
while ($row = oci_fetch_assoc($stmt)) {
    $items[] = $row;
}

include __DIR__ . '/../templates/header.php';
?>

<div class="grid">
    <div class="card">
        <h2>Add Equipment</h2>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="field">
                <label>Type</label>
                <input type="text" name="type">
            </div>
            <div class="field">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="field">
                <label>Total Quantity</label>
                <input type="number" name="total_qty" min="1" required>
            </div>
            <button type="submit">Add</button>
        </form>
    </div>

    <div class="card" style="grid-column: span 2;">
        <h2>Inventory</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Borrowed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="6">No equipment yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $eq): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($eq['NAME']); ?></td>
                        <td><?php echo htmlspecialchars($eq['TYPE']); ?></td>
                        <td><?php echo (int)$eq['TOTAL_QTY']; ?></td>
                        <td><?php echo (int)$eq['AVAILABLE_QTY']; ?></td>
                        <td><?php echo (int)$eq['BORROWED_QTY']; ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$eq['ID']; ?>">
                                <button type="submit" class="secondary" onclick="return confirm('Delete this item?')">Delete</button>
                            </form>
                            <details>
                                <summary>Edit</summary>
                                <form method="post">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo (int)$eq['ID']; ?>">
                                    <input type="hidden" name="borrowed_qty" value="<?php echo (int)$eq['BORROWED_QTY']; ?>">
                                    <div class="field">
                                        <label>Name</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($eq['NAME']); ?>" required>
                                    </div>
                                    <div class="field">
                                        <label>Type</label>
                                        <input type="text" name="type" value="<?php echo htmlspecialchars($eq['TYPE']); ?>">
                                    </div>
                                    <div class="field">
                                        <label>Description</label>
                                        <textarea name="description" rows="2"><?php echo htmlspecialchars($eq['DESCRIPTION']); ?></textarea>
                                    </div>
                                    <div class="field">
                                        <label>Total Quantity</label>
                                        <input type="number" name="total_qty" min="<?php echo (int)$eq['BORROWED_QTY']; ?>" value="<?php echo (int)$eq['TOTAL_QTY']; ?>" required>
                                    </div>
                                    <button type="submit">Save</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>

