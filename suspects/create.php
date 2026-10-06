<?php
$base_path    = '../';
$page_title   = 'Add Suspect';
$current_page = 'suspects';

require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();

// Only select ACTIVE cases ('Open' or 'Under Investigation') for dropdown
$cases = $db->query("
    SELECT case_id, case_number, title, status 
    FROM cases 
    WHERE status IN ('Open', 'Under Investigation') 
    ORDER BY case_number ASC
")->fetchAll();

$errors = [];
$preselected_case_id = (int)($_GET['case_id'] ?? 0);
$old = ['case_id' => $preselected_case_id];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $old = $_POST;

    if (empty(trim($_POST['full_name'] ?? '')))  $errors['full_name'] = 'Full name is required.';
    if (empty((int)($_POST['case_id'] ?? 0)))    $errors['case_id']   = 'Active case assignment is required.';
    if (!empty($_POST['gender']) && !in_array($_POST['gender'], ['Male','Female','Other'])) {
        $errors['gender'] = 'Invalid gender selection.';
    }

    if (empty($errors)) {
        try {
            // ACID Database Transaction
            $db->beginTransaction();

            $caseId = (int)$_POST['case_id'];

            // SELECT ... FOR UPDATE to lock case and verify active status
            $lockStmt = $db->prepare("SELECT case_id, case_number, status FROM cases WHERE case_id = :id FOR UPDATE");
            $lockStmt->execute([':id' => $caseId]);
            $caseRow = $lockStmt->fetch();

            if (!$caseRow) {
                throw new Exception('The selected case file was not found.');
            }

            if ($caseRow['status'] === 'Closed') {
                throw new Exception('Cannot link a suspect to a Closed case (' . $caseRow['case_number'] . '). Only active cases can receive new suspects.');
            }

            $stmt = $db->prepare("
                INSERT INTO suspects (full_name, date_of_birth, gender, address, identification_number, case_id)
                VALUES (:full_name, :dob, :gender, :address, :id_number, :case_id)
            ");
            $stmt->execute([
                ':full_name'  => trim($_POST['full_name']),
                ':dob'        => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                ':gender'     => !empty($_POST['gender']) ? $_POST['gender'] : null,
                ':address'    => trim($_POST['address'] ?? '') ?: null,
                ':id_number'  => trim($_POST['identification_number'] ?? '') ?: null,
                ':case_id'    => $caseId,
            ]);

            $db->commit();

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Suspect "' . e(trim($_POST['full_name'])) . '" added to active case ' . e($caseRow['case_number']) . '.'];
            header('Location: index.php');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $errors['_general'] = $e->getMessage() ?: 'A database transaction error occurred. Please try again.';
            error_log('Suspect registration error: ' . $e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<a href="index.php" class="back-link">&#8592; Back to Suspects</a>

<div class="page-header">
    <div class="page-title">&#43; Add Suspect</div>
    <div class="page-subtitle">Register a new suspect and link them to an active case file.</div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= e($errors['_general']) ?></div>
<?php endif; ?>

<?php if (empty($cases)): ?>
<div class="flash flash-error">No active cases available (status Open or Under Investigation). <a href="../cases/create.php">Open a new case</a> before adding suspects.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Suspect Information</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" placeholder="Suspect's full legal name">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Identification Number</label>
                    <input type="text" name="identification_number" value="<?= e($old['identification_number'] ?? '') ?>" placeholder="National ID, passport, etc.">
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
                    <?php if (!empty($errors['gender'])): ?><div class="form-error"><?= e($errors['gender']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Active Case File <span class="required">*</span></label>
                    <select name="case_id">
                        <option value="">— Assign to Active Case —</option>
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
                <button type="submit" class="btn btn-primary" <?= empty($cases) ? 'disabled' : '' ?>>Register Suspect</button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
