<?php
$base_path    = '../';
$current_page = 'suspects';

require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$suspect = $db->prepare("SELECT * FROM suspects WHERE suspect_id = :id");
$suspect->execute([':id' => $id]);
$suspect = $suspect->fetch();
if (!$suspect) { header('Location: index.php'); exit; }

$page_title = 'Edit Suspect';
$cases      = $db->query("SELECT case_id, case_number, title, status FROM cases ORDER BY case_number")->fetchAll();
$errors     = [];
$old        = $suspect;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $old = $_POST;

    if (empty(trim($_POST['full_name'] ?? '')))  $errors['full_name'] = 'Full name is required.';
    if (empty((int)($_POST['case_id'] ?? 0)))    $errors['case_id']   = 'Case assignment is required.';

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $caseId = (int)$_POST['case_id'];

            // SELECT ... FOR UPDATE on case to ensure it exists
            $caseLock = $db->prepare("SELECT case_id, case_number, status FROM cases WHERE case_id = :id FOR UPDATE");
            $caseLock->execute([':id' => $caseId]);
            if (!$caseLock->fetch()) {
                throw new Exception('The assigned case file was not found.');
            }

            // Lock suspect record
            $suspectLock = $db->prepare("SELECT suspect_id FROM suspects WHERE suspect_id = :id FOR UPDATE");
            $suspectLock->execute([':id' => $id]);
            if (!$suspectLock->fetch()) {
                throw new Exception('The suspect record no longer exists.');
            }

            $stmt = $db->prepare("
                UPDATE suspects
                SET full_name=:full_name, date_of_birth=:dob, gender=:gender,
                    address=:address, identification_number=:id_number, case_id=:case_id
                WHERE suspect_id=:id
            ");
            $stmt->execute([
                ':full_name' => trim($_POST['full_name']),
                ':dob'       => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                ':gender'    => !empty($_POST['gender']) ? $_POST['gender'] : null,
                ':address'   => trim($_POST['address'] ?? '') ?: null,
                ':id_number' => trim($_POST['identification_number'] ?? '') ?: null,
                ':case_id'   => $caseId,
                ':id'        => $id,
            ]);

            $db->commit();

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Suspect record updated successfully.'];
            header('Location: show.php?id=' . $id);
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors['_general'] = $e->getMessage() ?: 'A database error occurred.';
            error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<a href="show.php?id=<?= e($id) ?>" class="back-link">&#8592; Back to Suspect</a>

<div class="page-header">
    <div class="page-title">Edit Suspect</div>
    <div class="page-subtitle"><?= e($suspect['full_name']) ?></div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= e($errors['_general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Edit Suspect Information</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Identification Number</label>
                    <input type="text" name="identification_number" value="<?= e($old['identification_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" value="<?= e($old['date_of_birth'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">— Select —</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                        <option value="<?= e($g) ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Linked Case <span class="required">*</span></label>
                    <select name="case_id">
                        <?php foreach ($cases as $c): ?>
                        <option value="<?= e($c['case_id']) ?>" <?= ($old['case_id'] ?? 0) == $c['case_id'] ? 'selected' : '' ?>>
                            <?= e($c['case_number']) ?> &mdash; <?= e($c['title']) ?> (<?= e($c['status']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['case_id'])): ?><div class="form-error"><?= e($errors['case_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label>Address / Last Known Location</label>
                    <textarea name="address" style="min-height:70px;"><?= e($old['address'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="show.php?id=<?= e($id) ?>" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
