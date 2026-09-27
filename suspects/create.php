<?php
require_once '../config/database.php';
$base_path    = '../';
$page_title   = 'Add Suspect';
$current_page = 'suspects';

$db    = getDB();
$cases = $db->query("SELECT case_id, case_number, title FROM cases ORDER BY case_number")->fetchAll();

$errors = [];
$old    = ['case_id' => (int)($_GET['case_id'] ?? 0)];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    if (empty(trim($_POST['full_name'] ?? '')))  $errors['full_name'] = 'Full name is required.';
    if (empty((int)($_POST['case_id'] ?? 0)))    $errors['case_id']   = 'Case assignment is required.';
    if (!empty($_POST['gender']) && !in_array($_POST['gender'], ['Male','Female','Other'])) {
        $errors['gender'] = 'Invalid gender selection.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO suspects (full_name, date_of_birth, gender, address, identification_number, case_id)
                VALUES (:full_name, :dob, :gender, :address, :id_number, :case_id)
            ");
            $stmt->execute([
                ':full_name'  => trim($_POST['full_name']),
                ':dob'        => $_POST['date_of_birth'] ?: null,
                ':gender'     => $_POST['gender'] ?: null,
                ':address'    => trim($_POST['address'] ?? '') ?: null,
                ':id_number'  => trim($_POST['identification_number'] ?? '') ?: null,
                ':case_id'    => (int)$_POST['case_id'],
            ]);

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Suspect "' . htmlspecialchars(trim($_POST['full_name'])) . '" added to the system.'];
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            $errors['_general'] = 'A database error occurred. Please try again.';
            error_log($e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<a href="index.php" class="back-link">&#8592; Back to Suspects</a>

<div class="page-header">
    <div class="page-title">&#43; Add Suspect</div>
    <div class="page-subtitle">Register a new suspect and link them to a case file.</div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= htmlspecialchars($errors['_general']) ?></div>
<?php endif; ?>

<?php if (empty($cases)): ?>
<div class="flash flash-error">No cases available. <a href="../cases/create.php">Create a case</a> before adding suspects.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Suspect Information</div></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" placeholder="Suspect's full legal name">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Identification Number</label>
                    <input type="text" name="identification_number" value="<?= htmlspecialchars($old['identification_number'] ?? '') ?>" placeholder="National ID, passport, etc.">
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
                    <?php if (!empty($errors['gender'])): ?><div class="form-error"><?= htmlspecialchars($errors['gender']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Linked Case <span class="required">*</span></label>
                    <select name="case_id">
                        <option value="">— Assign to Case —</option>
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
                <button type="submit" class="btn btn-primary" <?= empty($cases) ? 'disabled' : '' ?>>Register Suspect</button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
