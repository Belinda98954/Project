<?php
/**
 * index.php — Landing Page
 * Owner: Gideon
 */
require_once __DIR__ . '/db.php';

// If logged in, go to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge — Freelancer &amp; Client Matchmaking</title>
    <meta name="description" content="SkillBridge connects clients with skilled freelancers through smart skill-based matchmaking. Post jobs, find talent, and get work done.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="login.php">Log In</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Find the Right Talent.<br>Get the Right Job.</h1>
        <p>SkillBridge matches clients with skilled freelancers based on expertise, budget, and availability. Post a job or showcase your skills — we'll handle the matchmaking.</p>
        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-outline" style="border-color:#fff; color:#fff;">Log In</a>
        </div>
    </section>

    <div class="container">
        <h2 class="page-title text-center mt-24">How It Works</h2>
        <div class="features-grid">
            <div class="feature-card">
                <h3>1. Create Your Profile</h3>
                <p>Sign up as a client looking for talent or a freelancer offering your skills. Add your expertise, hourly rate, and availability.</p>
            </div>
            <div class="feature-card">
                <h3>2. Post or Browse Jobs</h3>
                <p>Clients post jobs with required skills and budgets. Freelancers browse open opportunities that match their expertise.</p>
            </div>
            <div class="feature-card">
                <h3>3. Smart Matchmaking</h3>
                <p>Our matching system scores freelancers and jobs based on overlapping skills, helping you find the perfect fit fast.</p>
            </div>
        </div>

        <div class="text-center mt-24 mb-24">
            <h2 class="page-title">Ready to get started?</h2>
            <p class="page-subtitle">Join SkillBridge today and connect with the right people.</p>
            <a href="register.php" class="btn btn-primary">Create Free Account</a>
        </div>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
