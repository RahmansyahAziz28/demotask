<?php
$base_path    = '../';
$current_page = 'investigators';

require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM investigators WHERE investigator_id = :id");
$stmt->execute([':id' => $id]);
$inv = $stmt->fetch();
if (!$inv) { header('Location: index.php'); exit; }

$page_title = $inv['full_name'];

$cases_stmt = $db->prepare("
    SELECT c.case_id, c.case_number, c.title, c.crime_type, c.status, c.incident_date
    FROM cases c
    WHERE c.investigator_id = :id
    ORDER BY c.incident_date DESC
");
$cases_stmt->execute([':id' => $id]);
$cases = $cases_stmt->fetchAll();

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

<a href="index.php" class="back-link">&#8592; Back to Investigators</a>

<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="page-title"><?= e($inv['full_name']) ?></div>
        <div class="page-subtitle" style="margin-top:4px;">
            <?= e($inv['rank']) ?> &mdash; <?= e($inv['department']) ?>
        </div>
    </div>
    <div class="action-group">
        <a href="edit.php?id=<?= e($id) ?>" class="btn btn-secondary">Edit</a>
        <a href="delete.php?id=<?= e($id) ?>" class="btn btn-danger">Delete</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><div class="card-title">Officer Profile</div></div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Badge Number</div>
            <div class="detail-value mono"><?= e($inv['badge_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value"><?= e($inv['full_name']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Rank</div>
            <div class="detail-value"><?= e($inv['rank']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Department</div>
            <div class="detail-value"><?= e($inv['department']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone</div>
            <div class="detail-value"><?= e($inv['phone'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?= e($inv['email'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date Added</div>
            <div class="detail-value"><?= e(substr($inv['created_at'], 0, 16)) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Cases Assigned</div>
            <div class="detail-value" style="color:var(--gold);font-weight:700;"><?= count($cases) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Assigned Case Files (<?= count($cases) ?>)</div>
        <a href="../cases/create.php" class="btn btn-ghost btn-sm">Open New Case</a>
    </div>
    <div class="table-wrap">
        <?php if (empty($cases)): ?>
        <div class="empty-state" style="padding:28px;">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Cases Assigned</div>
            <div class="empty-state-text">This investigator has not been assigned to any case files yet.</div>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Title</th>
                    <th>Crime Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cases as $c): ?>
                <tr>
                    <td class="td-mono"><?= e($c['case_number']) ?></td>
                    <td><?= e($c['title']) ?></td>
                    <td class="td-muted"><?= e($c['crime_type']) ?></td>
                    <td class="td-muted"><?= e($c['incident_date']) ?></td>
                    <td><?= statusBadge($c['status']) ?></td>
                    <td><a href="../cases/show.php?id=<?= e($c['case_id']) ?>" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
