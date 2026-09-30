<?php
$base_path    = '../';
$current_page = 'suspects';

require_once '../includes/auth.php';
require_once '../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$suspect = $db->prepare("SELECT * FROM suspects WHERE suspect_id = :id");
$suspect->execute([':id' => $id]);
$suspect = $suspect->fetch();
if (!$suspect) { header('Location: index.php'); exit; }

$page_title = 'Edit Suspect';
$cases      = $db->query("SELECT case_id, case_number, title FROM cases ORDER BY case_number")->fetchAll();
$errors     = [];
$old        = $suspect;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    if (empty(trim($_POST['full_name'] ?? '')))  $errors['full_name'] = 'Full name is required.';
    if (empty((int)($_POST['case_id'] ?? 0)))    $errors['case_id']   = 'Case assignment is required.';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE suspects
                SET full_name=:full_name, date_of_birth=:dob, gender=:gender,
                    address=:address, identification_number=:id_number, case_id=:case_id
                WHERE suspect_id=:id
            ");
            $stmt->execute([
                ':full_name' => trim($_POST['full_name']),
                ':dob'       => $_POST['date_of_birth'] ?: null,
                ':gender'    => $_POST['gender'] ?: null,
                ':address'   => trim($_POST['address'] ?? '') ?: null,
                ':id_number' => trim($_POST['identification_number'] ?? '') ?: null,
                ':case_id'   => (int)$_POST['case_id'],
                ':id'        => $id,
            ]);

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Suspect record updated successfully.'];
            header('Location: show.php?id=' . $id);
            exit;

        } catch (PDOException $e) {
            $errors['_general'] = 'A database error occurred.';
            error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Suspect</a>

<div class="page-header">
    <div class="page-title">Edit Suspect</div>
    <div class="page-subtitle"><?= htmlspecialchars($suspect['full_name']) ?></div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= htmlspecialchars($errors['_general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Edit Suspect Information</div></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Identification Number</label>
                    <input type="text" name="identification_number" value="<?= htmlspecialchars($old['identification_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">— Select —</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Linked Case <span class="required">*</span></label>
                    <select name="case_id">
                        <?php foreach ($cases as $c): ?>
                        <option value="<?= $c['case_id'] ?>" <?= ($old['case_id'] ?? 0) == $c['case_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['case_number']) ?> — <?= htmlspecialchars($c['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['case_id'])): ?><div class="form-error"><?= htmlspecialchars($errors['case_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label>Address / Last Known Location</label>
                    <textarea name="address" style="min-height:70px;"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="show.php?id=<?= $id ?>" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
