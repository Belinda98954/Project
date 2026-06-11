<?php
/**
 * matches.php — Skill-Based Matchmaking
 * Owner: Gideon
 *
 * For Clients: shows freelancers whose skills overlap with their
 * posted jobs' required skills, sorted by match score.
 *
 * For Freelancers: shows jobs whose required skills match their
 * profile skills, sorted by match score.
 */
require_once __DIR__ . '/db.php';
requireLogin();

$user = getCurrentUser($pdo);
$role = $user['role'];

if ($role === 'client') {
    // ─── Client: Find freelancers matching job requirements ─────

    // Get all skill names required across this client's open jobs
    $stmt = $pdo->prepare('
        SELECT DISTINCT js.skill_name
        FROM job_skills js
        JOIN jobs j ON js.job_id = j.id
        WHERE j.client_id = ? AND j.status = ?
    ');
    $stmt->execute([$user['id'], 'open']);
    $requiredSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $matches = [];

    if (!empty($requiredSkills)) {
        // Find freelancers with overlapping skills, scored by count
        $placeholders = implode(',', array_fill(0, count($requiredSkills), '?'));
        $params = $requiredSkills;

        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, u.bio, u.hourly_rate, u.availability,
                   COUNT(s.skill_name) AS match_score
            FROM users u
            JOIN skills s ON u.id = s.user_id
            WHERE u.role = 'freelancer'
              AND LOWER(s.skill_name) IN ($placeholders)
            GROUP BY u.id
            ORDER BY match_score DESC
        ");
        $stmt->execute($params);
        $matches = $stmt->fetchAll();

        // Get total required skills for percentage display
        $totalRequired = count($requiredSkills);

        // Get each freelancer's full skills
        foreach ($matches as &$m) {
            $m['skills'] = getUserSkills($pdo, $m['id']);
            $m['total_required'] = $totalRequired;
        }
        unset($m);
    }

} else {
    // ─── Freelancer: Find jobs matching their skills ────────────

    $userSkills = getUserSkills($pdo, $user['id']);
    $matches = [];

    if (!empty($userSkills)) {
        $placeholders = implode(',', array_fill(0, count($userSkills), '?'));
        $params = $userSkills;

        $stmt = $pdo->prepare("
            SELECT j.id, j.title, j.description, j.budget_min, j.budget_max,
                   j.deadline, j.created_at, u.name AS client_name,
                   COUNT(js.skill_name) AS match_score
            FROM jobs j
            JOIN job_skills js ON j.id = js.job_id
            JOIN users u ON j.client_id = u.id
            WHERE j.status = 'open'
              AND LOWER(js.skill_name) IN ($placeholders)
            GROUP BY j.id
            ORDER BY match_score DESC
        ");
        $stmt->execute($params);
        $matches = $stmt->fetchAll();

        // Get each job's full skills and total count
        foreach ($matches as &$m) {
            $m['job_skills'] = getJobSkills($pdo, $m['id']);
            $m['total_skills'] = count($m['job_skills']);
        }
        unset($m);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches — SkillBridge</title>
    <meta name="description" content="View your skill-based matches on SkillBridge.">
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
            <a href="matches.php" class="active">Matches</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Your Matches</h1>

        <?php if ($role === 'client'): ?>
            <p class="page-subtitle">Freelancers whose skills match your job requirements, ranked by overlap.</p>

            <?php if (empty($requiredSkills)): ?>
                <div class="empty-state">
                    <p>Post a job first to see matching freelancers.</p>
                    <a href="post_job.php" class="btn btn-primary">Post a Job</a>
                </div>
            <?php elseif (empty($matches)): ?>
                <div class="empty-state">
                    <p>No freelancers match your required skills yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($matches as $m): ?>
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title"><?= h($m['name']) ?></h2>
                                <span class="card-meta"><?= h($m['email']) ?></span>
                            </div>
                            <span class="match-score"><?= $m['match_score'] ?>/<?= $m['total_required'] ?> skills matched</span>
                        </div>

                        <?php if ($m['bio']): ?>
                            <div class="card-body"><?= nl2br(h($m['bio'])) ?></div>
                        <?php endif; ?>

                        <div class="flex-between mb-8" style="flex-wrap:wrap; gap:8px;">
                            <span class="budget-range">$<?= number_format($m['hourly_rate'], 2) ?>/hr</span>
                            <span class="badge badge-<?= $m['availability'] === 'available' ? 'open' : 'closed' ?>">
                                <?= h(ucfirst($m['availability'])) ?>
                            </span>
                        </div>

                        <div class="skill-tags">
                            <?php foreach ($m['skills'] as $sk): ?>
                                <span class="skill-pill <?= in_array($sk, $requiredSkills) ? 'match' : '' ?>"><?= h($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <p class="page-subtitle">Jobs whose required skills match yours, ranked by overlap.</p>

            <?php if (empty($userSkills)): ?>
                <div class="empty-state">
                    <p>Add skills to your profile to see matching jobs.</p>
                    <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            <?php elseif (empty($matches)): ?>
                <div class="empty-state">
                    <p>No jobs match your skills yet. Check back later!</p>
                </div>
            <?php else: ?>
                <?php foreach ($matches as $m): ?>
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h2 class="card-title"><?= h($m['title']) ?></h2>
                                <span class="card-meta">Posted by <?= h($m['client_name']) ?> &middot; <?= h($m['created_at']) ?></span>
                            </div>
                            <span class="match-score"><?= $m['match_score'] ?>/<?= $m['total_skills'] ?> skills matched</span>
                        </div>

                        <div class="card-body"><?= nl2br(h(substr($m['description'], 0, 300))) ?><?= strlen($m['description']) > 300 ? '...' : '' ?></div>

                        <div class="flex-between mb-8" style="flex-wrap:wrap; gap:8px;">
                            <span class="budget-range">$<?= number_format($m['budget_min']) ?> – $<?= number_format($m['budget_max']) ?></span>
                            <span class="card-meta">Deadline: <?= h($m['deadline']) ?></span>
                        </div>

                        <div class="skill-tags">
                            <?php foreach ($m['job_skills'] as $sk): ?>
                                <span class="skill-pill <?= in_array($sk, $userSkills) ? 'match' : '' ?>"><?= h($sk) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="card-footer">
                            <a href="browse_jobs.php" class="btn btn-primary btn-sm">View &amp; Apply</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
