<?php
require_once __DIR__ . '/../helpers.php';
$user = current_user();
$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/styles.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><?php echo APP_NAME; ?></div>
    <nav class="nav">
        <a href="<?php echo BASE_URL; ?>/index.php">Equipment</a>
        <?php if ($user): ?>
            <a href="<?php echo BASE_URL; ?>/my_borrows.php">My Borrows</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Admin</a>
            <?php endif; ?>
            <span class="nav-user">Hello, <?php echo htmlspecialchars($user['username']); ?></span>
            <a class="btn" href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
            <a class="btn" href="<?php echo BASE_URL; ?>/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <?php if (!empty($flash)): ?>
        <div class="alerts">
            <?php foreach ($flash as $msg): ?>
                <div class="alert <?php echo 'alert-' . $msg['type']; ?>">
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

