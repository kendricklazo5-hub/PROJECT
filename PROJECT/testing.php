<?php
$conn = oci_connect("phpuser", "php123", "localhost/ORCLPDB");

if (!$conn) {
    $e = oci_error();
    echo "Connection failed: " . $e['message'];
} else {
    echo "Connected to Oracle successfully!";
    oci_close($conn);
}
?>
