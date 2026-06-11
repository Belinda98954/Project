<?php
/**
 * profile.php — View/Edit User Profile
 * Owner: Gideon
 *
 * Allows users to edit their name, bio, hourly rate, availability,
 * and manage skill tags dynamically.
 */
require_once __DIR__ . '/db.php';
requireLogin();

$user = getCurrentUser($pdo);
$role = $user['role'];
$skills = getUserSkills($pdo, $user['id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $bio          = trim($_POST['bio'] ?? '');
    $hourlyRate   = floatval($_POST['hourly_rate'] ?? 0);
    $availability = trim($_POST['availability'] ?? 'available');
    $skillsRaw    = trim($_POST['skills_hidden'] ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        // Update user profile
        $stmt = $pdo->prepare('UPDATE users SET name = ?, bio = ?, hourly_rate = ?, availability = ? WHERE id = ?');
        $stmt->execute([$name, $bio, $hourlyRate, $availability, $user['id']]);

        // Update session name
        $_SESSION['name'] = $name;

        // Update skills — delete old, insert new
        $stmt = $pdo->prepare('DELETE FROM skills WHERE user_id = ?');
        $stmt->execute([$user['id']]);

        if ($skillsRaw !== '') {
            $newSkills = array_map('trim', explode(',', $skillsRaw));
            $newSkills = array_filter($newSkills);
            $newSkills = array_unique(array_map('strtolower', $newSkills));

            $insert = $pdo->prepare('INSERT INTO skills (user_id, skill_name) VALUES (?, ?)');
            foreach ($newSkills as $sk) {
                $insert->execute([$user['id'], $sk]);
            }
        }

        $success = 'Profile updated successfully.';

        // Reload data
        $user = getCurrentUser($pdo);
        $skills = getUserSkills($pdo, $user['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — SkillBridge</title>
    <meta name="description" content="Edit your SkillBridge profile, skills, and availability.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="dashboard.php">Dashboard</a>
            <?php if ($role === 'client'): ?>
                <a href="post_job.php">Post Job</a>
                <a href="browse_freelancers.php">Browse Freelancers</a>
            <?php else: ?>
                <a href="browse_jobs.php">Browse Jobs</a>
            <?php endif; ?>
            <a href="matches.php">Matches</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1 class="page-title">Edit Profile</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form id="profile-form" method="POST" action="profile.php" class="card">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= h($user['name']) ?>">
                    <div class="form-error" id="name-error"></div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= h($user['email']) ?>" disabled style="background:#f0f0f0;">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?= h(ucfirst($user['role'])) ?>" disabled style="background:#f0f0f0;">
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" placeholder="Tell us about yourself..."><?= h($user['bio']) ?></textarea>
                </div>

                <?php if ($role === 'freelancer'): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hourly_rate">Hourly Rate ($)</label>
                            <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="0.01" value="<?= h($user['hourly_rate']) ?>">
                            <div class="form-error" id="hourly_rate-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="availability">Availability</label>
                            <select id="availability" name="availability">
                                <option value="available" <?= $user['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="busy" <?= $user['availability'] === 'busy' ? 'selected' : '' ?>>Busy</option>
                                <option value="unavailable" <?= $user['availability'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Skills -->
                <div class="form-group">
                    <label for="skill_input">Skills</label>
                    <div class="skill-input-wrapper">
                        <input type="text" id="skill_input" list="prefilled-skills" placeholder="Type a skill and press Enter">
                        <button type="button" id="add-skill-btn" class="btn btn-primary btn-sm">Add</button>
                    </div>
                    <datalist id="prefilled-skills">
                        <option value="Python">
                        <option value="Machine Learning (ML)">
                        <option value="JavaScript">
                        <option value="TypeScript">
                        <option value="React">
                        <option value="Node.js">
                        <option value="HTML">
                        <option value="CSS">
                        <option value="MongoDB">
                        <option value="SQLite">
                        <option value="PHP">
                        <option value="SQL">
                        <option value="Java">
                        <option value="C++">
                        <option value="Go">
                        <option value="Docker">
                        <option value="AWS">
                        <option value="Git">
                    </datalist>
                    <div class="form-error" id="skill_input-error"></div>
                    <div id="skill-tags-container" class="skill-tags mt-8"></div>
                    <input type="hidden" id="skills_hidden" name="skills_hidden" value="<?= h(implode(',', $skills)) ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Profile</button>
            </form>
        </div>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
