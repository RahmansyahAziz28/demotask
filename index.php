<?php
$base_path    = '';
$page_title   = 'Dashboard';
$current_page = 'dashboard';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

$db = getDB();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Real aggregate database queries
$total_cases       = (int)$db->query("SELECT COUNT(*) FROM cases")->fetchColumn();
$total_suspects    = (int)$db->query("SELECT COUNT(*) FROM suspects")->fetchColumn();
$open_cases        = (int)$db->query("SELECT COUNT(*) FROM cases WHERE status = 'Open'")->fetchColumn();
$active_cases      = (int)$db->query("SELECT COUNT(*) FROM cases WHERE status = 'Under Investigation'")->fetchColumn();
$total_invs        = (int)$db->query("SELECT COUNT(*) FROM investigators")->fetchColumn();

$recent_cases = $db->query("
    SELECT c.case_id, c.case_number, c.title, c.crime_type, c.status, c.incident_date,
           i.full_name AS investigator_name
    FROM cases c
    JOIN investigators i ON c.investigator_id = i.investigator_id
    ORDER BY c.created_at DESC
    LIMIT 6
")->fetchAll();

include __DIR__ . '/includes/header.php';

function dashboardStatusBadge(string $status): string {
    return match($status) {
        'Open'                 => '<span class="badge-status badge-open">Open</span>',
        'Under Investigation'  => '<span class="badge-status badge-investigation">Investigating</span>',
        'Closed'               => '<span class="badge-status badge-closed">Closed</span>',
        default                => '<span class="badge-status badge-default">' . e($status) . '</span>',
    };
}
?>

<?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>">
        <span><?= e($flash['msg']) ?></span>
    </div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">GPD Central Command</h1>
        <p class="page-subtitle">Real-time overview of criminal activities, case files, and active investigations in Gotham City.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="cases/create.php" class="btn btn-primary">&#43; Open New Case</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-number"><?= e($total_cases) ?></div>
            <div class="stat-label">Total Cases</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-number"><?= e($total_suspects) ?></div>
            <div class="stat-label">Total Suspects</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-number" style="color: #72cf9c;"><?= e($open_cases) ?></div>
            <div class="stat-label">Cases Status &lsquo;Open&rsquo;</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-number" style="color: #f0c36b;"><?= e($active_cases) ?></div>
            <div class="stat-label">Cases &lsquo;Under Investigation&rsquo;</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Recent Case Files</div>
        <a href="cases/index.php" class="btn btn-ghost btn-sm">View All Cases &rarr;</a>
    </div>
    <div class="table-wrap">
        <?php if (empty($recent_cases)): ?>
        <div class="no-records">
            <div class="no-records-icon">&#128193;</div>
            <div style="font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">No Cases Recorded</div>
            <div style="margin-bottom: 16px;">No criminal cases have been entered into the system yet.</div>
            <a href="cases/create.php" class="btn btn-primary">&#43; Open First Case</a>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Case No.</th>
                    <th>Title</th>
                    <th>Crime Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Assigned Investigator</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent_cases as $c): ?>
                <tr>
                    <td class="td-mono"><?= e($c['case_number']) ?></td>
                    <td><strong><?= e($c['title']) ?></strong></td>
                    <td class="td-muted"><?= e($c['crime_type']) ?></td>
                    <td><?= dashboardStatusBadge($c['status']) ?></td>
                    <td class="td-muted"><?= e($c['incident_date']) ?></td>
                    <td class="td-muted">&#128737; <?= e($c['investigator_name']) ?></td>
                    <td>
                        <a href="cases/show.php?id=<?= e($c['case_id']) ?>" class="btn btn-ghost btn-sm">View &rarr;</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-top:10px;">
    <a href="cases/create.php" class="btn btn-primary" style="justify-content:center;padding:14px;">
        &#43; Open New Case File
    </a>
    <a href="suspects/create.php" class="btn btn-secondary" style="justify-content:center;padding:14px;">
        &#43; Register Suspect
    </a>
    <a href="investigators/create.php" class="btn btn-ghost" style="justify-content:center;padding:14px;">
        &#43; Add Investigator (<?= e($total_invs) ?> active)
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
