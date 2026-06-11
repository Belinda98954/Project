<?php
/**
 * post_job.php — Client Posts a Job
 * Owner: Belinda
 *
 * Allows clients to create a job posting with title, description,
 * budget range, deadline, and required skill tags.
 */
require_once __DIR__ . '/db.php';
requireRole('client');

$user = getCurrentUser($pdo);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budgetMin   = floatval($_POST['budget_min'] ?? 0);
    $budgetMax   = floatval($_POST['budget_max'] ?? 0);
    $deadline    = trim($_POST['deadline'] ?? '');
    $skillsRaw   = trim($_POST['skills_hidden'] ?? '');

    // Server-side validation
    if ($title === '' || $description === '' || $deadline === '') {
        $error = 'All fields are required.';
    } elseif ($budgetMin < 0 || $budgetMax < 0) {
        $error = 'Budget values must be positive.';
    } elseif ($budgetMin >= $budgetMax) {
        $error = 'Maximum budget must be greater than minimum.';
    } elseif ($skillsRaw === '') {
        $error = 'Please add at least one required skill.';
    } else {
        // Insert job
        $stmt = $pdo->prepare('
            INSERT INTO jobs (client_id, title, description, budget_min, budget_max, deadline)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$user['id'], $title, $description, $budgetMin, $budgetMax, $deadline]);
        $jobId = $pdo->lastInsertId();

        // Insert job skills
        $jobSkills = array_map('trim', explode(',', $skillsRaw));
        $jobSkills = array_filter($jobSkills);
        $jobSkills = array_unique(array_map('strtolower', $jobSkills));

        $insert = $pdo->prepare('INSERT INTO job_skills (job_id, skill_name) VALUES (?, ?)');
        foreach ($jobSkills as $sk) {
            $insert->execute([$jobId, $sk]);
        }

        $success = 'Job posted successfully!';
        // Clear form values
        $title = $description = $deadline = $skillsRaw = '';
        $budgetMin = $budgetMax = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job — SkillBridge</title>
    <meta name="description" content="Post a new job on SkillBridge with required skills and budget.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="post_job.php" class="active">Post Job</a>
            <a href="browse_freelancers.php">Browse Freelancers</a>
            <a href="matches.php">Matches</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h1 class="page-title">Post a New Job</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form id="post-job-form" method="POST" action="post_job.php" class="card">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" value="<?= h($title ?? '') ?>" placeholder="e.g., Build a Landing Page">
                    <div class="form-error" id="title-error"></div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe the job in detail..."><?= h($description ?? '') ?></textarea>
                    <div class="form-error" id="description-error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="budget_min">Min Budget ($)</label>
                        <input type="number" id="budget_min" name="budget_min" min="0" step="1" value="<?= h($budgetMin ?? '') ?>" placeholder="e.g., 100">
                        <div class="form-error" id="budget_min-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="budget_max">Max Budget ($)</label>
                        <input type="number" id="budget_max" name="budget_max" min="0" step="1" value="<?= h($budgetMax ?? '') ?>" placeholder="e.g., 500">
                        <div class="form-error" id="budget_max-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input type="date" id="deadline" name="deadline" value="<?= h($deadline ?? '') ?>">
                    <div class="form-error" id="deadline-error"></div>
                </div>

                <div class="form-group">
                    <label for="skill_input">Required Skills</label>
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
                    <input type="hidden" id="skills_hidden" name="skills_hidden" value="">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Post Job</button>
            </form>
        </div>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
