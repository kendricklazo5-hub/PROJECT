<?php
require_once __DIR__ . '/helpers.php';
require_login();
$conn = db();

if (!is_post()) {
    header('Location: index.php');
    exit;
}

$equipment_id = (int)($_POST['equipment_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);

if ($equipment_id <= 0 || $qty <= 0) {
    flash('danger', 'Invalid borrow request.');
    header('Location: index.php');
    exit;
}

// Lock and check availability
$stmt = oci_parse($conn, "SELECT available_qty, total_qty FROM equipment WHERE id = :id FOR UPDATE");
oci_bind_by_name($stmt, ':id', $equipment_id);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
if (!$row) {
    flash('danger', 'Equipment not found.');
    header('Location: index.php');
    exit;
}

$available = (int)$row['AVAILABLE_QTY'];
if ($qty > $available) {
    flash('warning', 'Requested quantity exceeds available items.');
    header('Location: index.php');
    exit;
}

$newAvailable = $available - $qty;
$update = oci_parse($conn, "UPDATE equipment SET available_qty = :avail WHERE id = :id");
oci_bind_by_name($update, ':avail', $newAvailable);
oci_bind_by_name($update, ':id', $equipment_id);
oci_execute($update);

$borrow = oci_parse($conn, "INSERT INTO borrows (user_id, equipment_id, qty, status, borrowed_at) VALUES (:uid, :eid, :qty, 'borrowed', SYSTIMESTAMP)");
$userId = current_user()['id'];
oci_bind_by_name($borrow, ':uid', $userId);
oci_bind_by_name($borrow, ':eid', $equipment_id);
oci_bind_by_name($borrow, ':qty', $qty);
oci_execute($borrow);

flash('success', 'Equipment borrowed successfully.');
header('Location: my_borrows.php');
exit;

