<?php
$base_path    = '../';
$page_title   = 'Suspects';
$current_page = 'suspects';

require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$q  = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $db->prepare("
        SELECT s.*, c.case_id, c.case_number, c.title AS case_title, c.status AS case_status,
               c.crime_type, i.full_name AS investigator_name
        FROM suspects s
        JOIN cases c ON s.case_id = c.case_id
        LEFT JOIN investigators i ON c.investigator_id = i.investigator_id
        WHERE s.full_name ILIKE :q
           OR s.identification_number ILIKE :q
           OR s.address ILIKE :q
           OR c.case_number ILIKE :q
           OR c.title ILIKE :q
           OR c.crime_type ILIKE :q
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([':q' => "%{$q}%"]);
    $suspects = $stmt->fetchAll();
} else {
    $sql = "SELECT s.*, c.case_id, c.case_number, c.title AS case_title, c.status AS case_status,
                   c.crime_type, i.full_name AS investigator_name
            FROM suspects s
            JOIN cases c ON s.case_id = c.case_id
            LEFT JOIN investigators i ON c.investigator_id = i.investigator_id
            ORDER BY s.created_at DESC";
    $suspects = $db->query($sql)->fetchAll();
}

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';

function suspectCaseStatusBadge(string $status): string {
    return match($status) {
        'Open'                => '<span class="badge-status badge-open">Open</span>',
        'Under Investigation' => '<span class="badge-status badge-investigation">Investigating</span>',
        'Closed'              => '<span class="badge-status badge-closed">Closed</span>',
        default               => '<span class="badge-status badge-default">' . e($status) . '</span>',
    };
}
?>

<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Suspects Registry</div>
    <div class="page-subtitle">All persons of interest linked to active and closed case files.</div>
</div>

<div class="section-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
    <form method="GET" action="index.php" style="display:flex;align-items:center;gap:8px;flex:1;max-width:460px;">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search suspects by name, ID, or related case..." style="width:100%;padding:8px 12px;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:13px;">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($q !== ''): ?>
            <a href="index.php" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">&#43; Add Suspect</a>
</div>

<?php if ($q !== ''): ?>
<div style="margin-bottom:14px;color:var(--muted);font-size:12px;">
    Showing search results for &ldquo;<strong><?= e($q) ?></strong>&rdquo; (<?= count($suspects) ?> found)
</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($suspects)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title"><?= $q !== '' ? 'No Suspects Matched Search' : 'No Suspects Found' ?></div>
            <div class="empty-state-text"><?= $q !== '' ? 'Try adjusting your search query.' : 'No suspects registered in the system.' ?></div>
            <?php if ($q === ''): ?>
                <a href="create.php" class="btn btn-primary">Add First Suspect</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-ghost">View All Suspects</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>ID Number</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Handled Case Relation</th>
                    <th>Lead Investigator</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($suspects as $s): ?>
                <tr>
                    <td><strong><?= e($s['full_name']) ?></strong></td>
                    <td class="td-mono"><?= e($s['identification_number'] ?: '—') ?></td>
                    <td class="td-muted"><?= e($s['gender'] ?: '—') ?></td>
                    <td class="td-muted"><?= e($s['date_of_birth'] ?: '—') ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <a href="../cases/show.php?id=<?= e($s['case_id']) ?>" style="color:var(--gold);font-weight:600;font-size:12px;">
                                <?= e($s['case_number']) ?>
                            </a>
                            <?= suspectCaseStatusBadge($s['case_status'] ?? 'Open') ?>
                        </div>
                        <div class="td-muted" style="font-size:11px;"><?= e($s['case_title']) ?> (<?= e($s['crime_type']) ?>)</div>
                    </td>
                    <td class="td-muted">
                        <?= e($s['investigator_name'] ?: 'Unassigned') ?>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= e($s['suspect_id']) ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= e($s['suspect_id']) ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= e($s['suspect_id']) ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding:10px 14px;border-top:1px solid var(--border);color:var(--muted);font-size:11px;">
            <?= count($suspects) ?> record(s)
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
