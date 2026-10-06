<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title   = 'Investigator Login';
$current_page = 'login';
$base_path    = '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$old_identity = $_SESSION['old_login_id'] ?? '';
unset($_SESSION['old_login_id']);

include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-body">
            <?php if ($flash): ?>
                <div class="flash flash-<?= e($flash['type']) ?>">
                    <span><?= e($flash['msg']) ?></span>
                </div>
            <?php endif; ?>

            <form action="proses_login.php" method="POST" autocomplete="on">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="identity">Badge Number or Email <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="identity" 
                        name="identity" 
                        value="<?= e($old_identity) ?>" 
                        placeholder="e.g. GPD-001 or gordon@gpd.gotham.gov" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group" style="margin-bottom: 22px;">
                    <label for="password">Security Password <span class="required">*</span></label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your security passcode" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px;">
                    Sign In
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Need an investigator access account? <a href="register.php">Register New Account &rarr;</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
