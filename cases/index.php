<?php
$base_path    = '../';
$page_title   = 'Case Files';
$current_page = 'cases';

require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$q  = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $db->prepare("
        SELECT c.case_id, c.case_number, c.title, c.crime_type, c.location,
               c.incident_date, c.status, i.full_name AS investigator_name
        FROM cases c
        JOIN investigators i ON c.investigator_id = i.investigator_id
        WHERE c.case_number ILIKE :q
           OR c.title ILIKE :q
           OR c.crime_type ILIKE :q
           OR c.location ILIKE :q
           OR i.full_name ILIKE :q
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':q' => "%{$q}%"]);
    $cases = $stmt->fetchAll();
} else {
    $sql = "SELECT c.case_id, c.case_number, c.title, c.crime_type, c.location,
                   c.incident_date, c.status, i.full_name AS investigator_name
            FROM cases c
            JOIN investigators i ON c.investigator_id = i.investigator_id
            ORDER BY c.created_at DESC";
    $cases = $db->query($sql)->fetchAll();
}

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';

function statusBadge(string $status): string {
    return match($status) {
        'Open'                => '<span class="badge-status badge-open">Open</span>',
        'Under Investigation' => '<span class="badge-status badge-investigation">Investigating</span>',
        'Closed'              => '<span class="badge-status badge-closed">Closed</span>',
        default               => '<span class="badge-status badge-default">' . e($status) . '</span>',
    };
}
?>

<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>">
    <?= e($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Case Files</div>
    <div class="page-subtitle">All criminal cases registered in the Gotham Crime Records system.</div>
</div>

<div class="section-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <form method="GET" action="index.php" style="display:flex;align-items:center;gap:8px;flex:1;max-width:460px;">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by case #, title, crime type, location..." style="width:100%;padding:8px 12px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:13px;">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($q !== ''): ?>
            <a href="index.php" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">&#43; Open New Case</a>
</div>

<?php if ($q !== ''): ?>
<div style="margin-bottom:14px;color:var(--muted);font-size:12px;">
    Showing search results for &ldquo;<strong><?= e($q) ?></strong>&rdquo; (<?= count($cases) ?> found)
</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($cases)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title"><?= $q !== '' ? 'No Cases Matched Search' : 'No Cases Found' ?></div>
            <div class="empty-state-text"><?= $q !== '' ? 'Try adjusting your search query.' : 'No criminal cases have been entered yet.' ?></div>
            <?php if ($q === ''): ?>
                <a href="create.php" class="btn btn-primary">Open First Case</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-ghost">View All Cases</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Title</th>
                    <th>Crime Type</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Investigator</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cases as $c): ?>
                <tr>
                    <td class="td-mono"><?= e($c['case_number']) ?></td>
                    <td><?= e($c['title']) ?></td>
                    <td class="td-muted"><?= e($c['crime_type']) ?></td>
                    <td class="td-muted"><?= e($c['location']) ?></td>
                    <td class="td-muted"><?= e($c['incident_date']) ?></td>
                    <td><?= statusBadge($c['status']) ?></td>
                    <td class="td-muted"><?= e($c['investigator_name']) ?></td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= e($c['case_id']) ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= e($c['case_id']) ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= e($c['case_id']) ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding:10px 14px;border-top:1px solid var(--border);color:var(--muted);font-size:11px;">
            <?= count($cases) ?> record(s) found
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
