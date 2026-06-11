<?php
/**
 * login.php — User Login
 * Owner: Nelly
 */
require_once __DIR__ . '/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In — SkillBridge</title>
    <meta name="description" content="Log in to your SkillBridge account.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="login.php" class="active">Log In</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1 class="page-title text-center">Welcome Back</h1>
            <p class="page-subtitle text-center">Log in to your SkillBridge account.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form id="login-form" method="POST" action="login.php" class="card">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= h($email ?? '') ?>" placeholder="you@example.com">
                    <div class="form-error" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <div class="form-error" id="password-error"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log In</button>

                <p class="text-center mt-16">Don't have an account? <a href="register.php">Register</a></p>
            </form>
        </div>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
