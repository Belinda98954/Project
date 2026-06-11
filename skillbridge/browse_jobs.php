<?php
/**
 * browse_jobs.php — Freelancer Browses & Applies to Jobs
 * Owner: Belinda
 *
 * Lists open jobs with skill tags and budget. Freelancers can apply
 * with a cover letter. Shows skill match highlighting.
 */
require_once __DIR__ . '/db.php';
requireRole('freelancer');

$user = getCurrentUser($pdo);
$userSkills = getUserSkills($pdo, $user['id']);
$success = '';
$error = '';

// ─── Handle Application Submission ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $coverLetter = trim($_POST['cover_letter'] ?? '');

    if ($jobId <= 0) {
        $error = 'Invalid job.';
    } elseif ($coverLetter === '') {
        $error = 'Cover letter is required.';
    } else {
        // Check if already applied
        $stmt = $pdo->prepare('SELECT id FROM applications WHERE job_id = ? AND freelancer_id = ?');
        $stmt->execute([$jobId, $user['id']]);
        if ($stmt->fetch()) {
            $error = 'You have already applied to this job.';
        } else {
            // Check job exists and is open
            $stmt = $pdo->prepare('SELECT id FROM jobs WHERE id = ? AND status = ?');
            $stmt->execute([$jobId, 'open']);
            if (!$stmt->fetch()) {
                $error = 'This job is no longer available.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO applications (job_id, freelancer_id, cover_letter) VALUES (?, ?, ?)');
                $stmt->execute([$jobId, $user['id'], $coverLetter]);
                $success = 'Application submitted successfully!';
            }
        }
    }
}

// ─── Fetch Open Jobs ───────────────────────────────────────────
$stmt = $pdo->prepare('
    SELECT j.*, u.name AS client_name
    FROM jobs j
    JOIN users u ON j.client_id = u.id
    WHERE j.status = ?
    ORDER BY j.created_at DESC
');
$stmt->execute(['open']);
$jobs = $stmt->fetchAll();

// Check which jobs this freelancer already applied to
$stmt = $pdo->prepare('SELECT job_id FROM applications WHERE freelancer_id = ?');
$stmt->execute([$user['id']]);
$appliedJobs = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs — SkillBridge</title>
    <meta name="description" content="Browse open jobs on SkillBridge and apply with your skills.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_jobs.php" class="active">Browse Jobs</a>
            <a href="matches.php">Matches</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Browse Open Jobs</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <p>No open jobs right now. Check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job):
                $jobSkills = getJobSkills($pdo, $job['id']);
                $alreadyApplied = in_array($job['id'], $appliedJobs);
            ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><?= h($job['title']) ?></h2>
                            <span class="card-meta">Posted by <?= h($job['client_name']) ?> &middot; <?= h($job['created_at']) ?></span>
                        </div>
                        <span class="badge badge-open">Open</span>
                    </div>

                    <div class="card-body"><?= nl2br(h($job['description'])) ?></div>

                    <div class="flex-between mb-8" style="flex-wrap:wrap; gap:8px;">
                        <span class="budget-range">$<?= number_format($job['budget_min']) ?> – $<?= number_format($job['budget_max']) ?></span>
                        <span class="card-meta">Deadline: <?= h($job['deadline']) ?></span>
                    </div>

                    <!-- Skill Tags -->
                    <div class="skill-tags">
                        <?php foreach ($jobSkills as $sk): ?>
                            <span class="skill-pill <?= in_array($sk, $userSkills) ? 'match' : '' ?>"><?= h($sk) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Apply Section -->
                    <div class="card-footer">
                        <?php if ($alreadyApplied): ?>
                            <span class="badge badge-pending">Already Applied</span>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-sm apply-toggle-btn" data-job-id="<?= $job['id'] ?>">Apply Now</button>
                        <?php endif; ?>
                    </div>

                    <?php if (!$alreadyApplied): ?>
                        <div class="apply-form" id="apply-form-<?= $job['id'] ?>">
                            <form method="POST" action="browse_jobs.php" onsubmit="return validateApplication(<?= $job['id'] ?>)">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <div class="form-group">
                                    <label for="cover-letter-<?= $job['id'] ?>">Cover Letter</label>
                                    <textarea id="cover-letter-<?= $job['id'] ?>" name="cover_letter" placeholder="Explain why you're a good fit for this job..."></textarea>
                                    <div class="form-error" id="cover-letter-error-<?= $job['id'] ?>"></div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Submit Application</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
