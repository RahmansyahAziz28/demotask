<?php
$base_path    = '../';
$page_title   = 'Add Investigator';
$current_page = 'investigators';

require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/csrf.php';

$db     = getDB();
$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $old = $_POST;

    if (empty(trim($_POST['badge_number'] ?? ''))) $errors['badge_number'] = 'Badge number is required.';
    if (empty(trim($_POST['full_name'] ?? '')))    $errors['full_name']    = 'Full name is required.';
    if (empty(trim($_POST['rank'] ?? '')))         $errors['rank']         = 'Rank is required.';
    if (empty(trim($_POST['department'] ?? '')))   $errors['department']   = 'Department is required.';

    if (empty($errors)) {
        try {
            $plain_pw = trim($_POST['password'] ?? '');
            if ($plain_pw === '') {
                $plain_pw = 'password123';
            }
            $hash = password_hash($plain_pw, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO investigators (badge_number, full_name, rank, department, phone, email, password)
                VALUES (:badge, :name, :rank, :dept, :phone, :email, :password)
            ");
            $stmt->execute([
                ':badge'    => trim($_POST['badge_number']),
                ':name'     => trim($_POST['full_name']),
                ':rank'     => trim($_POST['rank']),
                ':dept'     => trim($_POST['department']),
                ':phone'    => trim($_POST['phone'] ?? '') ?: null,
                ':email'    => trim($_POST['email'] ?? '') ?: null,
                ':password' => $hash,
            ]);

            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Investigator "' . e(trim($_POST['full_name'])) . '" added to the roster. Default password: ' . e($plain_pw)];
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'duplicate')) {
                $errors['badge_number'] = 'This badge number is already in use.';
            } else {
                $errors['_general'] = 'A database error occurred.';
                error_log($e->getMessage());
            }
        }
    }
}

include '../includes/header.php';
?>

<a href="index.php" class="back-link">&#8592; Back to Investigators</a>

<div class="page-header">
    <div class="page-title">Add Investigator</div>
    <div class="page-subtitle">Register a new Gotham City Police Department investigator.</div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= e($errors['_general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Investigator Information</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-grid">

                <div class="form-group">
                    <label>Badge Number <span class="required">*</span></label>
                    <input type="text" name="badge_number" value="<?= e($old['badge_number'] ?? '') ?>" placeholder="e.g. GPD-006">
                    <?php if (!empty($errors['badge_number'])): ?><div class="form-error"><?= e($errors['badge_number']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" placeholder="Officer's full name">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Rank <span class="required">*</span></label>
                    <input type="text" name="rank" value="<?= e($old['rank'] ?? '') ?>" placeholder="e.g. Detective, Lieutenant, Captain">
                    <?php if (!empty($errors['rank'])): ?><div class="form-error"><?= e($errors['rank']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Department <span class="required">*</span></label>
                    <input type="text" name="department" value="<?= e($old['department'] ?? '') ?>" placeholder="e.g. Major Crimes Unit">
                    <?php if (!empty($errors['department'])): ?><div class="form-error"><?= e($errors['department']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" placeholder="555-xxxx">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="officer@gpd.gotham.gov">
                </div>

                <div class="form-group">
                    <label>Account Password</label>
                    <input type="password" name="password" placeholder="Leave empty for default (password123)">
                    <span class="form-hint">Default password is <code>password123</code> if left empty.</span>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Register Investigator</button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
