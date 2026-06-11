<?php
/**
 * browse_freelancers.php — Client Browses Freelancers
 * Owner: Belinda
 *
 * Allows clients to browse all available freelancers with their
 * skills, hourly rates, and availability status.
 */
require_once __DIR__ . '/db.php';
requireRole('client');

$user = getCurrentUser($pdo);

// Get all client's job skills for match highlighting
$stmt = $pdo->prepare('
    SELECT DISTINCT js.skill_name
    FROM job_skills js
    JOIN jobs j ON js.job_id = j.id
    WHERE j.client_id = ?
');
$stmt->execute([$user['id']]);
$clientJobSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch all freelancers
$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE role = 'freelancer'
    ORDER BY created_at DESC
");
$stmt->execute();
$freelancers = $stmt->fetchAll();

// Get skills for each freelancer
foreach ($freelancers as &$f) {
    $f['skills'] = getUserSkills($pdo, $f['id']);
}
unset($f);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Freelancers — SkillBridge</title>
    <meta name="description" content="Browse available freelancers on SkillBridge.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="post_job.php">Post Job</a>
            <a href="browse_freelancers.php" class="active">Browse Freelancers</a>
            <a href="matches.php">Matches</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Browse Freelancers</h1>
        <p class="page-subtitle">Find talented freelancers for your projects. Skills matching your job requirements are highlighted.</p>

        <?php if (empty($freelancers)): ?>
            <div class="empty-state">
                <p>No freelancers have registered yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($freelancers as $f): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title"><?= h($f['name']) ?></h2>
                            <span class="card-meta"><?= h($f['email']) ?></span>
                        </div>
                        <span class="badge badge-<?= $f['availability'] === 'available' ? 'open' : 'closed' ?>">
                            <?= h(ucfirst($f['availability'])) ?>
                        </span>
                    </div>

                    <?php if ($f['bio']): ?>
                        <div class="card-body"><?= nl2br(h($f['bio'])) ?></div>
                    <?php endif; ?>

                    <div class="flex-between mb-8" style="flex-wrap:wrap; gap:8px;">
                        <?php if ($f['hourly_rate'] > 0): ?>
                            <span class="budget-range">$<?= number_format($f['hourly_rate'], 2) ?>/hr</span>
                        <?php else: ?>
                            <span class="card-meta">Rate not set</span>
                        <?php endif; ?>
                        <span class="card-meta">Joined: <?= h(date('M j, Y', strtotime($f['created_at']))) ?></span>
                    </div>

                    <?php if (!empty($f['skills'])): ?>
                        <div class="skill-tags">
                            <?php foreach ($f['skills'] as $sk): ?>
                                <span class="skill-pill <?= in_array($sk, $clientJobSkills) ? 'match' : '' ?>"><?= h($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <span class="card-meta">No skills listed</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
