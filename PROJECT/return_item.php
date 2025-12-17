<?php
require_once __DIR__ . '/helpers.php';
require_login();
$conn = db();

if (!is_post()) {
    header('Location: my_borrows.php');
    exit;
}

$borrowId = (int)($_POST['borrow_id'] ?? 0);
$userId = current_user()['id'];

$stmt = oci_parse($conn, "SELECT b.id, b.equipment_id, b.qty, b.status, e.available_qty
                          FROM borrows b
                          JOIN equipment e ON e.id = b.equipment_id
                          WHERE b.id = :bid AND b.user_id = :uid");
oci_bind_by_name($stmt, ':bid', $borrowId);
oci_bind_by_name($stmt, ':uid', $userId);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);

if (!$row) {
    flash('danger', 'Borrow record not found.');
    header('Location: my_borrows.php');
    exit;
}

if ($row['STATUS'] !== 'borrowed') {
    flash('warning', 'Item already returned.');
    header('Location: my_borrows.php');
    exit;
}

$newAvail = (int)$row['AVAILABLE_QTY'] + (int)$row['QTY'];
$updateEquip = oci_parse($conn, "UPDATE equipment SET available_qty = :avail WHERE id = :eid");
oci_bind_by_name($updateEquip, ':avail', $newAvail);
oci_bind_by_name($updateEquip, ':eid', $row['EQUIPMENT_ID']);
oci_execute($updateEquip);

$updateBorrow = oci_parse($conn, "UPDATE borrows SET status = 'returned', returned_at = SYSTIMESTAMP WHERE id = :bid");
oci_bind_by_name($updateBorrow, ':bid', $borrowId);
oci_execute($updateBorrow);

flash('success', 'Item returned. Thank you!');
header('Location: my_borrows.php');
exit;

