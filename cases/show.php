<?php
require_once '../config/database.php';
$base_path    = '../';
$current_page = 'cases';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("
    SELECT c.*, i.full_name AS inv_name, i.badge_number, i.rank, i.department, i.phone, i.email
    FROM cases c
    JOIN investigators i ON c.investigator_id = i.investigator_id
    WHERE c.case_id = :id
");
$stmt->execute([':id' => $id]);
$case = $stmt->fetch();

if (!$case) { header('Location: index.php'); exit; }

$page_title = htmlspecialchars($case['case_number']);

$suspects = $db->prepare("SELECT * FROM suspects WHERE case_id = :id ORDER BY full_name");
$suspects->execute([':id' => $id]);
$suspects = $suspects->fetchAll();

include '../includes/header.php';

function statusBadge(string $status): string {
    return match($status) {
        'Open'                => '<span class="badge-status badge-open">Open</span>',
        'Under Investigation' => '<span class="badge-status badge-investigation">Under Investigation</span>',
        'Closed'              => '<span class="badge-status badge-closed">Closed</span>',
        default               => '<span class="badge-status badge-default">' . htmlspecialchars($status) . '</span>',
    };
}
?>

<a href="index.php" class="back-link">&#8592; Back to Case Files</a>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="page-title"><?= htmlspecialchars($case['title']) ?></div>
        <div class="page-subtitle" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
            <span class="td-mono" style="font-size:13px;"><?= htmlspecialchars($case['case_number']) ?></span>
            <?= statusBadge($case['status']) ?>
        </div>
    </div>
    <div class="action-group">
        <a href="edit.php?id=<?= $case['case_id'] ?>" class="btn btn-secondary">Edit Case</a>
        <a href="delete.php?id=<?= $case['case_id'] ?>" class="btn btn-danger">Delete</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div class="card-title">Case Details</div>
    </div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Case Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($case['case_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Crime Type</div>
            <div class="detail-value"><?= htmlspecialchars($case['crime_type']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Location</div>
            <div class="detail-value"><?= htmlspecialchars($case['location']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Incident Date</div>
            <div class="detail-value"><?= htmlspecialchars($case['incident_date']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value"><?= statusBadge($case['status']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date Opened</div>
            <div class="detail-value"><?= htmlspecialchars(substr($case['created_at'], 0, 16)) ?></div>
        </div>
    </div>
    <?php if (!empty($case['description'])): ?>
    <div class="detail-description">
        <div class="detail-label" style="margin-bottom:8px;">Case Notes</div>
        <?= htmlspecialchars($case['description']) ?>
    </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div class="card-title">Lead Investigator</div>
        <a href="../investigators/show.php?id=<?= $case['investigator_id'] ?>" class="btn btn-ghost btn-sm">View Profile</a>
    </div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value"><?= htmlspecialchars($case['inv_name']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Badge Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($case['badge_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Rank</div>
            <div class="detail-value"><?= htmlspecialchars($case['rank']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Department</div>
            <div class="detail-value"><?= htmlspecialchars($case['department']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Phone</div>
            <div class="detail-value"><?= htmlspecialchars($case['phone'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?= htmlspecialchars($case['email'] ?: '—') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Suspects (<?= count($suspects) ?>)</div>
        <a href="../suspects/create.php?case_id=<?= $case['case_id'] ?>" class="btn btn-primary btn-sm">&#43; Add Suspect</a>
    </div>
    <div class="table-wrap">
        <?php if (empty($suspects)): ?>
        <div class="empty-state" style="padding:28px;">
            <div class="empty-state-icon"></div>
            <div class="empty-state-title">No Suspects Registered</div>
            <div class="empty-state-text">No suspects have been linked to this case file.</div>
            <a href="../suspects/create.php?case_id=<?= $case['case_id'] ?>" class="btn btn-primary">Add Suspect</a>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>ID Number</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Address</th>
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
                    <td class="td-muted"><?= htmlspecialchars($s['address'] ?: '—') ?></td>
                    <td>
                        <div class="action-group">
                            <a href="../suspects/show.php?id=<?= $s['suspect_id'] ?>" class="btn btn-ghost btn-sm">View</a>
                            <a href="../suspects/edit.php?id=<?= $s['suspect_id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
