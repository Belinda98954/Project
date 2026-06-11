<?php
/**
 * register.php — User Registration
 * Owner: Nelly
 */
require_once __DIR__ . '/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? '';

    // Server-side validation
    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['freelancer', 'client'])) {
        $error = 'Invalid role selected.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hashed, $role]);

            // Auto-login
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $name;
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SkillBridge</title>
    <meta name="description" content="Create your SkillBridge account as a freelancer or client.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="login.php">Log In</a>
            <a href="register.php" class="active">Register</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1 class="page-title text-center">Create Account</h1>
            <p class="page-subtitle text-center">Join SkillBridge as a freelancer or client.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form id="register-form" method="POST" action="register.php" class="card">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= h($name ?? '') ?>" placeholder="Enter your full name">
                    <div class="form-error" id="name-error"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= h($email ?? '') ?>" placeholder="you@example.com">
                    <div class="form-error" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters">
                    <div class="form-error" id="password-error"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password">
                    <div class="form-error" id="confirm_password-error"></div>
                </div>

                <div class="form-group">
                    <label for="role">I am a...</label>
                    <select id="role" name="role">
                        <option value="">— Select Role —</option>
                        <option value="client" <?= (($role ?? '') === 'client') ? 'selected' : '' ?>>Client (I need work done)</option>
                        <option value="freelancer" <?= (($role ?? '') === 'freelancer') ? 'selected' : '' ?>>Freelancer (I offer services)</option>
                    </select>
                    <div class="form-error" id="role-error"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>

                <p class="text-center mt-16">Already have an account? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
