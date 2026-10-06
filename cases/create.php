<?php
$base_path    = '../';
$page_title   = 'Open New Case';
$current_page = 'cases';

require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db = getDB();
$investigators = $db->query("SELECT investigator_id, badge_number, full_name, rank FROM investigators ORDER BY full_name")->fetchAll();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $old = $_POST;

    $required = ['case_number','title','crime_type','location','incident_date','status','investigator_id'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[$field] = 'This field is required.';
        }
    }

    if (!in_array($_POST['status'] ?? '', ['Open','Under Investigation','Closed'])) {
        $errors['status'] = 'Invalid status selected.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Validate that the assigned investigator exists
            $invCheck = $db->prepare("SELECT investigator_id FROM investigators WHERE investigator_id = :id FOR UPDATE");
            $invCheck->execute([':id' => (int)$_POST['investigator_id']]);
            if (!$invCheck->fetch()) {
                throw new Exception('Selected investigator does not exist in the roster.');
            }

            $stmt = $db->prepare("
                INSERT INTO cases (case_number, title, crime_type, location, incident_date, status, description, investigator_id)
                VALUES (:case_number, :title, :crime_type, :location, :incident_date, :status, :description, :investigator_id)
            ");
            $stmt->execute([
                ':case_number'     => trim($_POST['case_number']),
                ':title'           => trim($_POST['title']),
                ':crime_type'      => trim($_POST['crime_type']),
                ':location'        => trim($_POST['location']),
                ':incident_date'   => $_POST['incident_date'],
                ':status'          => $_POST['status'],
                ':description'     => trim($_POST['description'] ?? ''),
                ':investigator_id' => (int)$_POST['investigator_id'],
            ]);

            $db->commit();

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Case ' . e(trim($_POST['case_number'])) . ' has been opened successfully.'];
            header('Location: index.php');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'duplicate')) {
                $errors['case_number'] = 'This case number already exists in the system.';
            } else {
                $errors['_general'] = $e->getMessage() ?: 'A database error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }
    }
}

include '../includes/header.php';
?>

<a href="index.php" class="back-link">&#8592; Back to Case Files</a>

<div class="page-header">
    <div class="page-title">&#43; Open New Case</div>
    <div class="page-subtitle">Register a new criminal case in the Gotham Crime Records system.</div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= e($errors['_general']) ?></div>
<?php endif; ?>

<?php if (empty($investigators)): ?>
<div class="flash flash-error">
    No investigators found in the system. <a href="../investigators/create.php">Add an investigator</a> before creating a case.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">Case Information</div>
    </div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-grid">

                <div class="form-group">
                    <label for="case_number">Case Number <span class="required">*</span></label>
                    <input type="text" id="case_number" name="case_number"
                           value="<?= e($old['case_number'] ?? '') ?>"
                           placeholder="e.g. GTH-2026-009">
                    <div class="form-hint">Unique identifier for this case file.</div>
                    <?php if (!empty($errors['case_number'])): ?>
                    <div class="form-error"><?= e($errors['case_number']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="title">Case Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title"
                           value="<?= e($old['title'] ?? '') ?>"
                           placeholder="Brief descriptive title">
                    <?php if (!empty($errors['title'])): ?>
                    <div class="form-error"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="crime_type">Crime Type <span class="required">*</span></label>
                    <input type="text" id="crime_type" name="crime_type"
                           value="<?= e($old['crime_type'] ?? '') ?>"
                           placeholder="e.g. Homicide, Burglary, Fraud">
                    <?php if (!empty($errors['crime_type'])): ?>
                    <div class="form-error"><?= e($errors['crime_type']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="location">Location <span class="required">*</span></label>
                    <input type="text" id="location" name="location"
                           value="<?= e($old['location'] ?? '') ?>"
                           placeholder="Crime scene location">
                    <?php if (!empty($errors['location'])): ?>
                    <div class="form-error"><?= e($errors['location']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="incident_date">Incident Date <span class="required">*</span></label>
                    <input type="date" id="incident_date" name="incident_date"
                           value="<?= e($old['incident_date'] ?? '') ?>">
                    <?php if (!empty($errors['incident_date'])): ?>
                    <div class="form-error"><?= e($errors['incident_date']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status">
                        <option value="">— Select Status —</option>
                        <?php foreach (['Open','Under Investigation','Closed'] as $s): ?>
                        <option value="<?= e($s) ?>" <?= ($old['status'] ?? '') === $s ? 'selected' : '' ?>>
                            <?= e($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['status'])): ?>
                    <div class="form-error"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="investigator_id">Lead Investigator <span class="required">*</span></label>
                    <select id="investigator_id" name="investigator_id">
                        <option value="">— Assign Investigator —</option>
                        <?php foreach ($investigators as $inv): ?>
                        <option value="<?= e($inv['investigator_id']) ?>"
                            <?= ($old['investigator_id'] ?? '') == $inv['investigator_id'] ? 'selected' : '' ?>>
                            <?= e($inv['full_name']) ?> (<?= e($inv['badge_number']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['investigator_id'])): ?>
                    <div class="form-error"><?= e($errors['investigator_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group full">
                    <label for="description">Case Description</label>
                    <textarea id="description" name="description" placeholder="Detailed description of the incident, evidence, and notes..."><?= e($old['description'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" <?= empty($investigators) ? 'disabled' : '' ?>>
                    Open Case File
                </button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
