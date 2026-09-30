<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title   = 'Investigator Registration';
$current_page = 'register';
$base_path    = '';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = $_SESSION['register_errors'] ?? [];
unset($_SESSION['register_errors']);

$old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_old']);

include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card auth-wide">

        <div class="auth-body">
            <?php if ($flash): ?>
                <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
                    <span><?= htmlspecialchars($flash['msg']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['_general'])): ?>
                <div class="flash flash-error">
                    <span><?= htmlspecialchars($errors['_general']) ?></span>
                </div>
            <?php endif; ?>

            <form action="proses_register.php" method="POST" autocomplete="off">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="badge_number">Badge Number <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="badge_number" 
                            name="badge_number" 
                            value="<?= htmlspecialchars($old['badge_number'] ?? '') ?>" 
                            placeholder="e.g. GPD-006" 
                            required
                        >
                        <?php if (!empty($errors['badge_number'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['badge_number']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" 
                            placeholder="e.g. Richard Grayson" 
                            required
                        >
                        <?php if (!empty($errors['full_name'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="rank">Official Rank <span class="required">*</span></label>
                        <select id="rank" name="rank" required>
                            <option value="">-- Select Rank --</option>
                            <?php
                            $ranks = ['Detective', 'Officer', 'Sergeant', 'Lieutenant', 'Captain', 'Inspector', 'Commissioner'];
                            $selected_rank = $old['rank'] ?? 'Detective';
                            foreach ($ranks as $r):
                            ?>
                                <option value="<?= $r ?>" <?= $selected_rank === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['rank'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['rank']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="department">Department Unit <span class="required">*</span></label>
                        <select id="department" name="department" required>
                            <option value="">-- Select Department --</option>
                            <?php
                            $depts = [
                                'Major Crimes Unit',
                                'Special Crimes Unit',
                                'Organized Crime Unit',
                                'Homicide Division',
                                'Narcotics Division',
                                'Cyber & Digital Forensics',
                                'Tactical Operations'
                            ];
                            $selected_dept = $old['department'] ?? 'Major Crimes Unit';
                            foreach ($depts as $d):
                            ?>
                                <option value="<?= $d ?>" <?= $selected_dept === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['department'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['department']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Department Email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>" 
                            placeholder="officer@gpd.gotham.gov" 
                            required
                        >
                        <?php if (!empty($errors['email'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Contact Phone</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?= htmlspecialchars($old['phone'] ?? '') ?>" 
                            placeholder="555-0199"
                        >
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['phone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Account Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="At least 6 characters" 
                            required
                        >
                        <?php if (!empty($errors['password'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Retype password" 
                            required
                        >
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <div class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="padding: 11px 24px;">
                        Register
                    </button>
                    <a href="login.php" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>

        <div class="auth-footer">
            Already have an investigator account? <a href="login.php">&larr; Return to Sign In</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
