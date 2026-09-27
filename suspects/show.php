<?php
require_once '../config/database.php';
$base_path    = '../';
$current_page = 'suspects';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("
    SELECT s.*, c.case_number, c.title AS case_title, c.status AS case_status,
           c.crime_type, c.location, c.incident_date
    FROM suspects s
    JOIN cases c ON s.case_id = c.case_id
    WHERE s.suspect_id = :id
");
$stmt->execute([':id' => $id]);
$suspect = $stmt->fetch();
if (!$suspect) { header('Location: index.php'); exit; }

$page_title = htmlspecialchars($suspect['full_name']);

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

<a href="index.php" class="back-link">&#8592; Back to Suspects</a>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <div class="page-title"><?= htmlspecialchars($suspect['full_name']) ?></div>
        <div class="page-subtitle" style="margin-top:4px;">
            Suspect — <?= htmlspecialchars($suspect['identification_number'] ?: 'No ID on record') ?>
        </div>
    </div>
    <div class="action-group">
        <a href="edit.php?id=<?= $id ?>" class="btn btn-secondary">Edit</a>
        <a href="delete.php?id=<?= $id ?>" class="btn btn-danger">Delete</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><div class="card-title">Personal Information</div></div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['full_name']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Identification Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($suspect['identification_number'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Gender</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['gender'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date of Birth</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['date_of_birth'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Date Registered</div>
            <div class="detail-value"><?= htmlspecialchars(substr($suspect['created_at'], 0, 16)) ?></div>
        </div>
    </div>
    <?php if (!empty($suspect['address'])): ?>
    <div class="detail-description">
        <div class="detail-label" style="margin-bottom:6px;">Address / Last Known Location</div>
        <?= htmlspecialchars($suspect['address']) ?>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Linked Case File</div>
        <a href="../cases/show.php?id=<?= $suspect['case_id'] ?>" class="btn btn-ghost btn-sm">View Full Case</a>
    </div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Case Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($suspect['case_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Case Title</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['case_title']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Crime Type</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['crime_type']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Location</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['location']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Incident Date</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['incident_date']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Case Status</div>
            <div class="detail-value"><?= statusBadge($suspect['case_status']) ?></div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
