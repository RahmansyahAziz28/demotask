<?php
$base_path    = '../';
$current_page = 'investigators';

require_once '../includes/auth.php';
require_once '../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$inv = $db->prepare("SELECT * FROM investigators WHERE investigator_id = :id");
$inv->execute([':id' => $id]);
$inv = $inv->fetch();
if (!$inv) { header('Location: index.php'); exit; }

$page_title = 'Edit Investigator';
$errors     = [];
$old        = $inv;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    if (empty(trim($_POST['badge_number'] ?? ''))) $errors['badge_number'] = 'Badge number is required.';
    if (empty(trim($_POST['full_name'] ?? '')))    $errors['full_name']    = 'Full name is required.';
    if (empty(trim($_POST['rank'] ?? '')))         $errors['rank']         = 'Rank is required.';
    if (empty(trim($_POST['department'] ?? '')))   $errors['department']   = 'Department is required.';

    if (empty($errors)) {
        try {
            $new_pw = trim($_POST['password'] ?? '');
            if ($new_pw !== '') {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE investigators
                    SET badge_number=:badge, full_name=:name, rank=:rank, department=:dept, phone=:phone, email=:email, password=:password
                    WHERE investigator_id=:id
                ");
                $params = [
                    ':badge'    => trim($_POST['badge_number']),
                    ':name'     => trim($_POST['full_name']),
                    ':rank'     => trim($_POST['rank']),
                    ':dept'     => trim($_POST['department']),
                    ':phone'    => trim($_POST['phone'] ?? '') ?: null,
                    ':email'    => trim($_POST['email'] ?? '') ?: null,
                    ':password' => $hash,
                    ':id'       => $id,
                ];
            } else {
                $stmt = $db->prepare("
                    UPDATE investigators
                    SET badge_number=:badge, full_name=:name, rank=:rank, department=:dept, phone=:phone, email=:email
                    WHERE investigator_id=:id
                ");
                $params = [
                    ':badge' => trim($_POST['badge_number']),
                    ':name'  => trim($_POST['full_name']),
                    ':rank'  => trim($_POST['rank']),
                    ':dept'  => trim($_POST['department']),
                    ':phone' => trim($_POST['phone'] ?? '') ?: null,
                    ':email' => trim($_POST['email'] ?? '') ?: null,
                    ':id'    => $id,
                ];
            }
            $stmt->execute($params);

            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
                $_SESSION['user_name']    = trim($_POST['full_name']);
                $_SESSION['badge_number'] = trim($_POST['badge_number']);
                $_SESSION['rank']         = trim($_POST['rank']);
                $_SESSION['department']   = trim($_POST['department']);
            }
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Investigator record updated successfully.'];
            header('Location: show.php?id=' . $id);
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

<a href="show.php?id=<?= $id ?>" class="back-link">&#8592; Back to Investigator</a>

<div class="page-header">
    <div class="page-title">Edit Investigator</div>
    <div class="page-subtitle"><?= htmlspecialchars($inv['full_name']) ?> — <?= htmlspecialchars($inv['badge_number']) ?></div>
</div>

<?php if (!empty($errors['_general'])): ?>
<div class="flash flash-error"><?= htmlspecialchars($errors['_general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title">Edit Investigator Information</div></div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>Badge Number <span class="required">*</span></label>
                    <input type="text" name="badge_number" value="<?= htmlspecialchars($old['badge_number'] ?? '') ?>">
                    <?php if (!empty($errors['badge_number'])): ?><div class="form-error"><?= htmlspecialchars($errors['badge_number']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>">
                    <?php if (!empty($errors['full_name'])): ?><div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Rank <span class="required">*</span></label>
                    <input type="text" name="rank" value="<?= htmlspecialchars($old['rank'] ?? '') ?>">
                    <?php if (!empty($errors['rank'])): ?><div class="form-error"><?= htmlspecialchars($errors['rank']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Department <span class="required">*</span></label>
                    <input type="text" name="department" value="<?= htmlspecialchars($old['department'] ?? '') ?>">
                    <?php if (!empty($errors['department'])): ?><div class="form-error"><?= htmlspecialchars($errors['department']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Change Security Password</label>
                    <input type="password" name="password" placeholder="Leave empty to keep existing password">
                    <span class="form-hint">Only enter a value if you wish to reset or change this officer's password.</span>
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
