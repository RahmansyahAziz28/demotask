<?php
$base_path    = '../';
$current_page = 'cases';

require_once '../includes/auth.php';
require_once '../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$case = $db->prepare("SELECT * FROM cases WHERE case_id = :id");
$case->execute([':id' => $id]);
$case = $case->fetch();
if (!$case) { header('Location: index.php'); exit; }

$page_title   = 'Edit ' . $case['case_number'];
$investigators = $db->query("SELECT investigator_id, badge_number, full_name FROM investigators ORDER BY full_name")->fetchAll();

$errors = [];
$old    = $case;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $required = ['case_number','title','crime_type','location','incident_date','status','investigator_id'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[$field] = 'This field is required.';
        }
    }

    if (!in_array($_POST['status'] ?? '', ['Open','Under Investigation','Closed'])) {
        $errors['status'] = 'Invalid status.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE cases
                SET case_number=:case_number, title=:title, crime_type=:crime_type,
                    location=:location, incident_date=:incident_date, status=:status,
                    description=:description, investigator_id=:investigator_id
                WHERE case_id=:id
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
                ':id'              => $id,
            ]);

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Case updated successfully.'];
            header('Location: show.php?id=' . $id);
            exit;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'duplicate')) {
                $errors['case_number'] = 'This case number already exists.';
            } else {
                $errors['_general'] = 'A database error occurred.';
                error_log($e->getMessage());
            }
        }
    }
}

include '../includes/header.php';
?>

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Case</a>

<div class="page-header">
    <div class="page-title">Edit Case</div>
    <div class="page-subtitle"><?= htmlspecialchars($case['case_number']) ?> — <?= htmlspecialchars($case['title']) ?></div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= htmlspecialchars($errors['_general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Edit Case Information</div></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>Case Number <span class="required">*</span></label>
                    <input type="text" name="case_number" value="<?= htmlspecialchars($old['case_number'] ?? '') ?>">
                    <?php if (!empty($errors['case_number'])): ?><div class="form-error"><?= htmlspecialchars($errors['case_number']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Case Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>">
                    <?php if (!empty($errors['title'])): ?><div class="form-error"><?= htmlspecialchars($errors['title']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Crime Type <span class="required">*</span></label>
                    <input type="text" name="crime_type" value="<?= htmlspecialchars($old['crime_type'] ?? '') ?>">
                    <?php if (!empty($errors['crime_type'])): ?><div class="form-error"><?= htmlspecialchars($errors['crime_type']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Location <span class="required">*</span></label>
                    <input type="text" name="location" value="<?= htmlspecialchars($old['location'] ?? '') ?>">
                    <?php if (!empty($errors['location'])): ?><div class="form-error"><?= htmlspecialchars($errors['location']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Incident Date <span class="required">*</span></label>
                    <input type="date" name="incident_date" value="<?= htmlspecialchars($old['incident_date'] ?? '') ?>">
                    <?php if (!empty($errors['incident_date'])): ?><div class="form-error"><?= htmlspecialchars($errors['incident_date']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
                    <select name="status">
                        <?php foreach (['Open','Under Investigation','Closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($old['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['status'])): ?><div class="form-error"><?= htmlspecialchars($errors['status']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Lead Investigator <span class="required">*</span></label>
                    <select name="investigator_id">
                        <?php foreach ($investigators as $inv): ?>
                        <option value="<?= $inv['investigator_id'] ?>"
                            <?= ($old['investigator_id'] ?? '') == $inv['investigator_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($inv['full_name']) ?> (<?= htmlspecialchars($inv['badge_number']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['investigator_id'])): ?><div class="form-error"><?= htmlspecialchars($errors['investigator_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label>Case Description</label>
                    <textarea name="description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
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
