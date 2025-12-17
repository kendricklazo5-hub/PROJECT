<?php
require_once __DIR__ . '/config.php';

/**
 * Create or reuse an OCI connection.
 */
function db() {
    static $conn = null;
    if ($conn) {
        return $conn;
    }
    $conn = oci_connect(DB_USER, DB_PASS, DB_DSN);
    if (!$conn) {
        $e = oci_error();
        die('Database connection failed: ' . $e['message']);
    }
    return $conn;
}

/**
 * Close the shared OCI connection (optional).
 */
function db_close() {
    global $conn;
    if ($conn) {
        oci_close($conn);
        $conn = null;
    }
}

