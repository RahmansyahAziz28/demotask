<?php
$base_path    = '../';
$page_title   = 'Case Files';
$current_page = 'cases';

require_once '../config/database.php';

$db = getDB();

$sql = "SELECT c.case_id, c.case_number, c.title, c.crime_type, c.location,
               c.incident_date, c.status, i.full_name AS investigator_name
        FROM cases c
        JOIN investigators i ON c.investigator_id = i.investigator_id
        ORDER BY c.created_at DESC";

$cases = $db->query($sql)->fetchAll();

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';

function statusBadge(string $status): string {
    return match($status) {
        'Open'                => '<span class="badge-status badge-open">Open</span>',
        'Under Investigation' => '<span class="badge-status badge-investigation">Investigating</span>',
        'Closed'              => '<span class="badge-status badge-closed">Closed</span>',
        default               => '<span class="badge-status badge-default">' . htmlspecialchars($status) . '</span>',
    };
}
?>

<?php if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Case Files</div>
    <div class="page-subtitle">All criminal cases registered in the Gotham Crime Records system.</div>
</div>

<div class="section-header">
    <div></div>
    <a href="create.php" class="btn btn-primary">&#43; Open New Case</a>
</div>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($cases)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Cases Found</div>
            <div class="empty-state-text">No criminal cases have been entered yet.</div>
            <a href="create.php" class="btn btn-primary">Open First Case</a>
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
                    <td class="td-mono"><?= htmlspecialchars($c['case_number']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['crime_type']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['location']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['incident_date']) ?></td>
                    <td><?= statusBadge($c['status']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['investigator_name']) ?></td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= $c['case_id'] ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= $c['case_id'] ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= $c['case_id'] ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
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
