<?php
$base_path    = '../';
$current_page = 'suspects';

require_once '../includes/auth.php';
require_once '../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("
    SELECT s.*, c.case_number, c.title AS case_title
    FROM suspects s JOIN cases c ON s.case_id = c.case_id
    WHERE s.suspect_id = :id
");
$stmt->execute([':id' => $id]);
$suspect = $stmt->fetch();
if (!$suspect) { header('Location: index.php'); exit; }

$page_title = 'Delete Suspect';
$error      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $del = $db->prepare("DELETE FROM suspects WHERE suspect_id = :id");
        $del->execute([':id' => $id]);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Suspect "' . htmlspecialchars($suspect['full_name']) . '" has been removed from the registry.'];
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        $error = 'A database error occurred. Please try again.';
        error_log($e->getMessage());
    }
}

include '../includes/header.php';
?>

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Suspect</a>

<div class="page-header">
    <div class="page-title" style="color:var(--danger);">Delete Suspect</div>
    <div class="page-subtitle">This action is permanent and cannot be undone.</div>
</div>

<?php if ($error): ?>
<div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Record to be Deleted</div></div>
    <div class="detail-grid">
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['full_name']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">ID Number</div>
            <div class="detail-value mono"><?= htmlspecialchars($suspect['identification_number'] ?: '—') ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Linked Case</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['case_number']) ?> — <?= htmlspecialchars($suspect['case_title']) ?></div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Gender</div>
            <div class="detail-value"><?= htmlspecialchars($suspect['gender'] ?: '—') ?></div>
        </div>
    </div>
</div>

<div class="danger-zone">
    <h3>Confirm Permanent Deletion</h3>
    <p>
        You are about to permanently remove <strong><?= htmlspecialchars($suspect['full_name']) ?></strong>
        from the Gotham Crime Records suspect registry. This cannot be undone.
    </p>
    <form method="POST" style="display:inline;">
        <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"
                onclick="return confirm('Permanently delete this suspect record?')">
            Yes, Delete Suspect
        </button>
        <a href="show.php?id=<?= $id ?>" class="btn btn-ghost" style="margin-left:8px;">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
