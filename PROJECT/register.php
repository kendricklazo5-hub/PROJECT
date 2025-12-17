<?php
require_once __DIR__ . '/helpers.php';
$conn = db(); // Oracle connection

if (current_user()) {
    header('Location: index.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    // Get role from form (user or admin); default to user if invalid
    $role = $_POST['role'] ?? 'user';
    $role = ($role === 'admin' || $role === 'user') ? $role : 'user';

    // Validation
    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    }

    // Check if username exists
    $stmt = oci_parse($conn, "SELECT id FROM users WHERE LOWER(username) = :u");
    oci_bind_by_name($stmt, ':u', $username);
    oci_execute($stmt);
    if (oci_fetch_assoc($stmt)) {
        $errors[] = 'Username already taken.';
    }

    // Insert user if no errors
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = oci_parse($conn, "INSERT INTO users (username, password_hash, role) VALUES (:u, :p, :r)");
        oci_bind_by_name($insert, ':u', $username);
        oci_bind_by_name($insert, ':p', $hash);
        oci_bind_by_name($insert, ':r', $role);

        $r = oci_execute($insert, OCI_COMMIT_ON_SUCCESS);
        if ($r) {
            flash('success', 'Account created. Please log in.');
            header('Location: login.php');
            exit;
        } else {
            $e = oci_error($insert);
            $errors[] = "Database error: " . $e['message'];
        }
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="card" style="max-width: 500px;">
    <h2>Create Account</h2>
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
            <input type="password" name="password" required minlength="4">
        </div>
        <div class="field">
            <label>Role</label>
            <select name="role" required>
                <option value="user" selected>User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Login</a></p>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
