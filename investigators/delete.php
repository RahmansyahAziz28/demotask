<?php
require_once '../config/database.php';
$base_path    = '../';
$current_page = 'investigators';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$inv = $db->prepare("SELECT * FROM investigators WHERE investigator_id = :id");
$inv->execute([':id' => $id]);
$inv = $inv->fetch();
if (!$inv) { header('Location: index.php'); exit; }

$page_title = 'Delete Investigator';

$case_count = $db->prepare("SELECT COUNT(*) FROM cases WHERE investigator_id = :id");
$case_count->execute([':id' => $id]);
$case_count = (int)$case_count->fetchColumn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $del = $db->prepare("DELETE FROM investigators WHERE investigator_id = :id");
        $del->execute([':id' => $id]);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Investigator "' . htmlspecialchars($inv['full_name']) . '" has been removed from the roster.'];
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'foreign key') || str_contains($e->getMessage(), 'violates')) {
            $error = 'This investigator cannot be deleted because they are assigned to one or more active case files. Reassign or close those cases first.';
        } else {
            $error = 'A database error occurred. Please try again.';
            error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Investigator</a>

<div class="page-header">
    <div class="page-title" style="color:var(--danger);">Delete Investigator</div>
    <div class="page-subtitle">This action is permanent and cannot be undone.</div>
</div>

<?php if ($error): ?>
<div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Record to be Deleted</div></div>
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
            <div class="detail-label">Active Cases</div>
            <div class="detail-value" style="color:<?= $case_count > 0 ? 'var(--danger)' : 'var(--success)' ?>">
                <?= $case_count ?> case(s) <?= $case_count > 0 ? '— must be reassigned first' : '' ?>
            </div>
        </div>
    </div>
</div>

<?php if ($case_count > 0): ?>
<div class="flash flash-error">
    <strong>Cannot delete:</strong> This investigator is still assigned to <?= $case_count ?> case(s).
    <a href="show.php?id=<?= $id ?>">View their cases</a> and reassign them before deleting this record.
</div>
<?php else: ?>
<div class="danger-zone">
    <h3>Confirm Permanent Deletion</h3>
    <p>
        You are about to permanently remove <strong><?= htmlspecialchars($inv['full_name']) ?></strong>
        (<?= htmlspecialchars($inv['badge_number']) ?>) from the Gotham City Police Department records.
        This action cannot be undone.
    </p>
    <form method="POST" style="display:inline;">
        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"
                onclick="return confirm('Permanently remove this investigator from the system?')">
            Yes, Delete Investigator
        </button>
        <a href="show.php?id=<?= $id ?>" class="btn btn-ghost" style="margin-left:8px;">Cancel</a>
    </form>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
