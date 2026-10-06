<?php
$base_path    = '../';
$page_title   = 'Investigators';
$current_page = 'investigators';

require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$q  = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $db->prepare("
        SELECT i.*, COUNT(c.case_id) AS case_count
        FROM investigators i
        LEFT JOIN cases c ON i.investigator_id = c.investigator_id
        WHERE i.badge_number ILIKE :q
           OR i.full_name ILIKE :q
           OR i.rank ILIKE :q
           OR i.department ILIKE :q
           OR i.email ILIKE :q
        GROUP BY i.investigator_id
        ORDER BY i.full_name
    ");
    $stmt->execute([':q' => "%{$q}%"]);
    $investigators = $stmt->fetchAll();
} else {
    $sql = "SELECT i.*, COUNT(c.case_id) AS case_count
            FROM investigators i
            LEFT JOIN cases c ON i.investigator_id = c.investigator_id
            GROUP BY i.investigator_id
            ORDER BY i.full_name";
    $investigators = $db->query($sql)->fetchAll();
}

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';
?>

<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Investigator Roster</div>
    <div class="page-subtitle">All Gotham City Police Department investigators registered in the system.</div>
</div>

<div class="section-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <form method="GET" action="index.php" style="display:flex;align-items:center;gap:8px;flex:1;max-width:460px;">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by badge #, name, rank, department..." style="width:100%;padding:8px 12px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:13px;">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($q !== ''): ?>
            <a href="index.php" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">&#43; Add Investigator</a>
</div>

<?php if ($q !== ''): ?>
<div style="margin-bottom:14px;color:var(--muted);font-size:12px;">
    Showing search results for &ldquo;<strong><?= e($q) ?></strong>&rdquo; (<?= count($investigators) ?> found)
</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($investigators)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title"><?= $q !== '' ? 'No Investigators Matched Search' : 'No Investigators Found' ?></div>
            <div class="empty-state-text"><?= $q !== '' ? 'Try adjusting your search query.' : 'No investigators registered in the system.' ?></div>
            <?php if ($q === ''): ?>
                <a href="create.php" class="btn btn-primary">Add First Investigator</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-ghost">View All Investigators</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Badge #</th>
                    <th>Full Name</th>
                    <th>Rank</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Cases</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($investigators as $inv): ?>
                <tr>
                    <td class="td-mono"><?= e($inv['badge_number']) ?></td>
                    <td><?= e($inv['full_name']) ?></td>
                    <td class="td-muted"><?= e($inv['rank']) ?></td>
                    <td class="td-muted"><?= e($inv['department']) ?></td>
                    <td class="td-muted"><?= e($inv['phone'] ?: '—') ?></td>
                    <td>
                        <span class="badge-status badge-default"><?= e($inv['case_count']) ?></span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= e($inv['investigator_id']) ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= e($inv['investigator_id']) ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= e($inv['investigator_id']) ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding:10px 14px;border-top:1px solid var(--border);color:var(--muted);font-size:11px;">
            <?= count($investigators) ?> record(s)
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
