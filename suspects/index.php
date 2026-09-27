<?php
require_once '../config/database.php';
$base_path    = '../';
$page_title   = 'Suspects';
$current_page = 'suspects';

$db     = getDB();
$search = trim($_GET['search'] ?? '');

$sql    = "SELECT s.*, c.case_number, c.title AS case_title
           FROM suspects s
           JOIN cases c ON s.case_id = c.case_id
           WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (s.full_name ILIKE :full_name OR s.identification_number ILIKE :identification_number OR c.case_number ILIKE :case_number)";
    $search_term = "%{$search}%";
    $params[':full_name'] = $search_term;
    $params[':identification_number'] = $search_term;
    $params[':case_number'] = $search_term;
}

$sql .= " ORDER BY s.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$suspects = $stmt->fetchAll();

if (session_status() === PHP_SESSION_NONE) session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include '../includes/header.php';
?>

<?php if ($flash): ?>
<div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="page-title">Suspects Registry</div>
    <div class="page-subtitle">All persons of interest linked to active and closed case files.</div>
</div>

<div class="section-header">
    <form class="search-form" method="GET">
        <input type="search" name="search" placeholder="Search name, ID, case..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
        <?php if ($search): ?><a href="index.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <a href="create.php" class="btn btn-primary">&#43; Add Suspect</a>
</div>

<div class="card">
    <div class="table-wrap">
        <?php if (empty($suspects)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Suspects Found</div>
            <div class="empty-state-text"><?= $search ? 'No suspects match your search.' : 'No suspects registered in the system.' ?></div>
            <?php if (!$search): ?><a href="create.php" class="btn btn-primary">Add First Suspect</a><?php endif; ?>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>ID Number</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Linked Case</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($suspects as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                    <td class="td-mono"><?= htmlspecialchars($s['identification_number'] ?: '—') ?></td>
                    <td class="td-muted"><?= htmlspecialchars($s['gender'] ?: '—') ?></td>
                    <td class="td-muted"><?= htmlspecialchars($s['date_of_birth'] ?: '—') ?></td>
                    <td>
                        <a href="../cases/show.php?id=<?= $s['case_id'] ?>" style="color:var(--steel);font-size:12px;">
                            <?= htmlspecialchars($s['case_number']) ?>
                        </a>
                        <div class="td-muted" style="font-size:11px;"><?= htmlspecialchars($s['case_title']) ?></div>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="show.php?id=<?= $s['suspect_id'] ?>" class="btn btn-ghost btn-sm"><span>View</span></a>
                            <a href="edit.php?id=<?= $s['suspect_id'] ?>" class="btn btn-secondary btn-sm"><span>Edit</span></a>
                            <a href="delete.php?id=<?= $s['suspect_id'] ?>" class="btn btn-danger btn-sm"><span>Delete</span></a>
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
