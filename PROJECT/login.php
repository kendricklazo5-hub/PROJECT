<?php
require_once __DIR__ . '/helpers.php';
$conn = db();

if (current_user()) {
    header('Location: index.php');
    exit;
}

$errors = [];
if (is_post()) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = oci_parse($conn, "SELECT id, username, password_hash, role FROM users WHERE LOWER(username) = :u");
    $lower = strtolower($username);
    oci_bind_by_name($stmt, ':u', $lower);
    oci_execute($stmt);
    $user = oci_fetch_assoc($stmt);

    if (!$user || !password_verify($password, $user['PASSWORD_HASH'])) {
        $errors[] = 'Invalid username or password.';
    } else {
        $_SESSION['user'] = [
            'id' => $user['ID'],
            'username' => $user['USERNAME'],
            'role' => $user['ROLE']
        ];
        flash('success', 'Welcome back, ' . $user['USERNAME'] . '!');
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="card" style="max-width: 500px;">
    <h2>Login</h2>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>
    <form method="post">
        <div class="field">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>

