<?php
require_once '../config/database.php';
$base_path    = '../';
$current_page = 'cases';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$case = $db->prepare("SELECT * FROM cases WHERE case_id = :id");
$case->execute([':id' => $id]);
$case = $case->fetch();
if (!$case) { header('Location: index.php'); exit; }

$page_title = 'Delete Case';

$suspect_count = $db->prepare("SELECT COUNT(*) FROM suspects WHERE case_id = :id");
$suspect_count->execute([':id' => $id]);
$suspect_count = (int)$suspect_count->fetchColumn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM cases WHERE case_id = :id");
        $stmt->execute([':id' => $id]);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Case ' . htmlspecialchars($case['case_number']) . ' has been permanently deleted.'];
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'foreign key') || str_contains($e->getMessage(), 'violates')) {
            $error = 'This case cannot be deleted because it still has suspects linked to it. Remove all suspects from this case first.';
        } else {
            $error = 'A database error occurred. Please try again.';
            error_log($e->getMessage());
        }
    }
}

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

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Case</a>

<div class="page-header">
    <div class="page-title" style="color:var(--danger);">Delete Case</div>
    <div class="page-subtitle">This action is permanent and cannot be undone.</div>
</div>

<?php if ($error): ?>
<div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Record to be Deleted</div></div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Case Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($case['case_number']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Title</div>
            <div class="detail-value"><?= htmlspecialchars($case['title']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value"><?= statusBadge($case['status']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Linked Suspects</div>
            <div class="detail-value" style="color:<?= $suspect_count > 0 ? 'var(--danger)' : 'var(--success)' ?>">
                <?= $suspect_count ?> suspect(s)
                <?= $suspect_count > 0 ? '— must be removed first' : '' ?>
            </div>
        </div>
    </div>
</div>

<?php if ($suspect_count > 0): ?>
<div class="flash flash-error">
    <strong>Cannot delete:</strong> This case has <?= $suspect_count ?> suspect(s) linked to it.
    <a href="show.php?id=<?= $id ?>">View suspects</a> and remove them before deleting this case.
</div>
<?php else: ?>
<div class="danger-zone">
    <h3>Confirm Permanent Deletion</h3>
    <p>
        You are about to permanently delete case <strong><?= htmlspecialchars($case['case_number']) ?></strong>
        — "<em><?= htmlspecialchars($case['title']) ?></em>".
        This record will be removed from the Gotham Crime Records database and cannot be recovered.
    </p>
    <form method="POST" style="display:inline;">
        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"
                onclick="return confirm('Final confirmation: permanently delete this case file?')">
            Yes, Delete This Case
        </button>
        <a href="show.php?id=<?= $id ?>" class="btn btn-ghost" style="margin-left:8px;">Cancel</a>
    </form>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
