<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        flash('warning', 'Please log in first.');
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        flash('danger', 'Admin access required.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function sanitize($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

