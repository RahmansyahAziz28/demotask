<?php
require_once '../config/database.php';
$base_path    = '../';
$current_page = 'investigators';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM investigators WHERE investigator_id = :id");
$stmt->execute([':id' => $id]);
$inv = $stmt->fetch();
if (!$inv) { header('Location: index.php'); exit; }

$page_title = htmlspecialchars($inv['full_name']);

$cases_stmt = $db->prepare("
    SELECT c.case_id, c.case_number, c.title, c.crime_type, c.status, c.incident_date
    FROM cases c
    WHERE c.investigator_id = :id
    ORDER BY c.incident_date DESC
");
$cases_stmt->execute([':id' => $id]);
$cases = $cases_stmt->fetchAll();

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

<a href="index.php" class="back-link">&#8592; Back to Investigators</a>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="page-title"><?= htmlspecialchars($inv['full_name']) ?></div>
        <div class="page-subtitle" style="margin-top:4px;">
            <?= htmlspecialchars($inv['rank']) ?> &mdash; <?= htmlspecialchars($inv['department']) ?>
        </div>
    </div>
    <div class="action-group">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">Edit</a>
        <a href="delete.php?id=<?= $id ?>" class="btn btn-danger">Delete</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><div class="card-title">Officer Profile</div></div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Badge Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($inv['badge_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value"><?= htmlspecialchars($inv['full_name']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Rank</div>
            <div class="detail-value"><?= htmlspecialchars($inv['rank']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Department</div>
            <div class="detail-value"><?= htmlspecialchars($inv['department']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone</div>
            <div class="detail-value"><?= htmlspecialchars($inv['phone'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?= htmlspecialchars($inv['email'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date Added</div>
            <div class="detail-value"><?= htmlspecialchars(substr($inv['created_at'], 0, 16)) ?></div>
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
                    <td class="td-mono"><?= htmlspecialchars($c['case_number']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['crime_type']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($c['incident_date']) ?></td>
                    <td><?= statusBadge($c['status']) ?></td>
                    <td><a href="../cases/show.php?id=<?= $c['case_id'] ?>" class="btn btn-ghost btn-sm">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
