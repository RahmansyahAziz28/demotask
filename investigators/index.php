<?php
$base_path    = '../';
$page_title   = 'Investigators';
$current_page = 'investigators';

require_once '../config/database.php';

$db = getDB();

$sql = "SELECT i.*,
               COUNT(c.case_id) AS case_count
        FROM investigators i
        LEFT JOIN cases c ON i.investigator_id = c.investigator_id
        GROUP BY i.investigator_id ORDER BY i.full_name";

$investigators = $db->query($sql)->fetchAll();

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';
?>

<?php if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Investigator Roster</div>
    <div class="page-subtitle">All Gotham City Police Department investigators registered in the system.</div>
</div>

<div class="section-header">
    <div></div>
    <a href="create.php" class="btn btn-primary">&#43; Add Investigator</a>
</div>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($investigators)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Investigators Found</div>
            <div class="empty-state-text">No investigators registered in the system.</div>
            <a href="create.php" class="btn btn-primary">Add First Investigator</a>
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
                    <td class="td-mono"><?= htmlspecialchars($inv['badge_number']) ?></td>
                    <td><?= htmlspecialchars($inv['full_name']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($inv['rank']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($inv['department']) ?></td>
                    <td class="td-muted"><?= htmlspecialchars($inv['phone'] ?: '—') ?></td>
                    <td>
                        <span class="badge-status badge-default"><?= $inv['case_count'] ?></span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= $inv['investigator_id'] ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= $inv['investigator_id'] ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= $inv['investigator_id'] ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
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
