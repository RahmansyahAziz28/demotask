<?php
require_once '../config/database.php';
$base_path    = '../';
$page_title   = 'Investigators';
$current_page = 'investigators';

$db     = getDB();
$search = trim($_GET['search'] ?? '');

$sql    = "SELECT i.*,
                  COUNT(c.case_id) AS case_count
           FROM investigators i
           LEFT JOIN cases c ON i.investigator_id = c.investigator_id
           WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (i.full_name ILIKE :s OR i.badge_number ILIKE :s OR i.department ILIKE :s OR i.rank ILIKE :s)";
    $params[':s'] = "%{$search}%";
}

$sql .= " GROUP BY i.investigator_id ORDER BY i.full_name";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$investigators = $stmt->fetchAll();

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
    <form class="search-form" method="GET">
        <input type="search" name="search" placeholder="Search name, badge, dept..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
        <?php if ($search): ?><a href="index.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">&#43; Add Investigator</a>
</div>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($investigators)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Investigators Found</div>
            <div class="empty-state-text"><?= $search ? 'No investigators match your search.' : 'No investigators registered in the system.' ?></div>
            <?php if (!$search): ?><a href="create.php" class="btn btn-primary">Add First Investigator</a><?php endif; ?>
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
