<?php
/**
 * dashboard.php — Role-Based Dashboard
 * Owner: Gideon
 *
 * Shows stats and quick actions based on user role (client or freelancer).
 * Also handles application accept/reject for clients.
 */
require_once __DIR__ . '/db.php';
requireLogin();

$user = getCurrentUser($pdo);
$role = $user['role'];

// ─── Handle Accept / Reject Application ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'client') {
    $appId = (int)($_POST['application_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($appId > 0 && in_array($action, ['accepted', 'rejected'])) {
        // Verify this application belongs to a job owned by this client
        $stmt = $pdo->prepare('
            SELECT a.id FROM applications a
            JOIN jobs j ON a.job_id = j.id
            WHERE a.id = ? AND j.client_id = ?
        ');
        $stmt->execute([$appId, $user['id']]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
            $stmt->execute([$action, $appId]);
        }
    }
    header('Location: dashboard.php');
    exit;
}

// ─── Gather Stats ──────────────────────────────────────────────
if ($role === 'client') {
    // Jobs posted
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE client_id = ?');
    $stmt->execute([$user['id']]);
    $jobsPosted = $stmt->fetchColumn();

    // Applications received
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.client_id = ?
    ');
    $stmt->execute([$user['id']]);
    $appsReceived = $stmt->fetchColumn();

    // Pending applications
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.client_id = ? AND a.status = ?
    ');
    $stmt->execute([$user['id'], 'pending']);
    $pendingApps = $stmt->fetchColumn();

    // Fetch pending applications with details
    $stmt = $pdo->prepare('
        SELECT a.*, j.title AS job_title, u.name AS freelancer_name, u.email AS freelancer_email
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON a.freelancer_id = u.id
        WHERE j.client_id = ? AND a.status = ?
        ORDER BY a.created_at DESC
    ');
    $stmt->execute([$user['id'], 'pending']);
    $pendingApplications = $stmt->fetchAll();

} else {
    // Applications submitted
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE freelancer_id = ?');
    $stmt->execute([$user['id']]);
    $appsSubmitted = $stmt->fetchColumn();

    // Accepted applications
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE freelancer_id = ? AND status = ?');
    $stmt->execute([$user['id'], 'accepted']);
    $appsAccepted = $stmt->fetchColumn();

    // Skills count
    $skills = getUserSkills($pdo, $user['id']);
    $skillCount = count($skills);

    // Recent applications
    $stmt = $pdo->prepare('
        SELECT a.*, j.title AS job_title
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.freelancer_id = ?
        ORDER BY a.created_at DESC
        LIMIT 5
    ');
    $stmt->execute([$user['id']]);
    $recentApplications = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SkillBridge</title>
    <meta name="description" content="Your SkillBridge dashboard. Manage jobs, applications, and matches.">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">SkillBridge</a>
        <button class="navbar-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <?php if ($role === 'client'): ?>
                <a href="post_job.php">Post Job</a>
                <a href="browse_freelancers.php">Browse Freelancers</a>
            <?php else: ?>
                <a href="browse_jobs.php">Browse Jobs</a>
            <?php endif; ?>
            <a href="matches.php">Matches</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Welcome, <?= h($user['name']) ?></h1>
        <p class="page-subtitle">You are logged in as a <strong><?= h(ucfirst($role)) ?></strong>.</p>

        <!-- ─── Stats ────────────────────────────────────────── -->
        <div class="stats-grid">
            <?php if ($role === 'client'): ?>
                <div class="stat-card">
                    <span class="stat-number"><?= $jobsPosted ?></span>
                    <span class="stat-label">Jobs Posted</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $appsReceived ?></span>
                    <span class="stat-label">Applications Received</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $pendingApps ?></span>
                    <span class="stat-label">Pending Review</span>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <span class="stat-number"><?= $appsSubmitted ?></span>
                    <span class="stat-label">Applications Sent</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $appsAccepted ?></span>
                    <span class="stat-label">Accepted</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= $skillCount ?></span>
                    <span class="stat-label">Skills Listed</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ─── Quick Actions ────────────────────────────────── -->
        <div class="card">
            <h2 class="card-title mb-16">Quick Actions</h2>
            <div class="flex gap-8" style="flex-wrap:wrap;">
                <?php if ($role === 'client'): ?>
                    <a href="post_job.php" class="btn btn-primary">Post a New Job</a>
                    <a href="browse_freelancers.php" class="btn btn-outline">Browse Freelancers</a>
                    <a href="matches.php" class="btn btn-outline">View Matches</a>
                <?php else: ?>
                    <a href="browse_jobs.php" class="btn btn-primary">Browse Jobs</a>
                    <a href="matches.php" class="btn btn-outline">View Matches</a>
                    <a href="profile.php" class="btn btn-outline">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── Pending Applications (Client) ────────────────── -->
        <?php if ($role === 'client' && !empty($pendingApplications)): ?>
            <h2 class="section-title mt-24">Pending Applications</h2>
            <?php foreach ($pendingApplications as $app): ?>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <span class="card-title"><?= h($app['freelancer_name']) ?></span>
                            <span class="card-meta" style="display:block;"><?= h($app['freelancer_email']) ?></span>
                        </div>
                        <span class="badge badge-pending">Pending</span>
                    </div>
                    <div class="card-meta">Applied to: <strong><?= h($app['job_title']) ?></strong></div>
                    <div class="card-body"><?= nl2br(h($app['cover_letter'])) ?></div>
                    <div class="card-footer">
                        <span class="card-meta"><?= h($app['created_at']) ?></span>
                        <div class="application-actions">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                <input type="hidden" name="action" value="accepted">
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                <input type="hidden" name="action" value="rejected">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ─── Recent Applications (Freelancer) ─────────────── -->
        <?php if ($role === 'freelancer' && !empty($recentApplications)): ?>
            <h2 class="section-title mt-24">Recent Applications</h2>
            <?php foreach ($recentApplications as $app): ?>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><?= h($app['job_title']) ?></span>
                        <span class="badge badge-<?= $app['status'] ?>"><?= h(ucfirst($app['status'])) ?></span>
                    </div>
                    <div class="card-body"><?= nl2br(h(substr($app['cover_letter'], 0, 200))) ?><?= strlen($app['cover_letter']) > 200 ? '...' : '' ?></div>
                    <div class="card-meta"><?= h($app['created_at']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer class="footer">&copy; 2026 SkillBridge. All rights reserved.</footer>
    <script src="app.js"></script>
</body>
</html>
