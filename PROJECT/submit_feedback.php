<?php
require_once __DIR__ . '/helpers.php';
require_login();
$conn = db();

if (!is_post()) {
    header('Location: my_borrows.php');
    exit;
}

$borrowId = (int)($_POST['borrow_id'] ?? 0);
$feedback = sanitize($_POST['feedback'] ?? '');
$userId = current_user()['id'];

if ($borrowId <= 0 || $feedback === '') {
    flash('warning', 'Feedback cannot be empty.');
    header('Location: my_borrows.php');
    exit;
}

$stmt = oci_parse($conn, "UPDATE borrows SET feedback = :fb WHERE id = :bid AND user_id = :uid");
oci_bind_by_name($stmt, ':fb', $feedback);
oci_bind_by_name($stmt, ':bid', $borrowId);
oci_bind_by_name($stmt, ':uid', $userId);
oci_execute($stmt);

flash('success', 'Feedback saved.');
header('Location: my_borrows.php');
exit;

