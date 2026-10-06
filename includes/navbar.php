<?php
$base = $base_path ?? '';
$cur  = $current_page ?? 'dashboard';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name    = $_SESSION['user_name'] ?? 'Investigator';
$badge_number = $_SESSION['badge_number'] ?? '';
$rank         = $_SESSION['rank'] ?? '';
?>
<header class="gpd-top-navbar" id="mainNavbar">
    <div class="navbar-container">
        <a href="<?= $base ?>index.php" class="navbar-brand">
            <div class="brand-badge">&#9878;</div>
            <div class="brand-text">
                <span class="brand-title">GOTHAM P.D.</span>
                <span class="brand-sub">Crime Records Terminal</span>
            </div>
        </a>

        <button class="navbar-toggle-btn" id="navToggleBtn" onclick="toggleNavMenu()" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="navbar-nav-group" id="navbarNavGroup">
            <ul class="nav-links">
                <li>
                    <a href="<?= $base ?>index.php" class="nav-link-item <?= $cur === 'dashboard' ? 'active' : '' ?>">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= $base ?>cases/index.php" class="nav-link-item <?= $cur === 'cases' ? 'active' : '' ?>">
                        Case Files
                    </a>
                </li>
                <li>
                    <a href="<?= $base ?>suspects/index.php" class="nav-link-item <?= $cur === 'suspects' ? 'active' : '' ?>">
                        Suspects
                    </a>
                </li>
                <li>
                    <a href="<?= $base ?>investigators/index.php" class="nav-link-item <?= $cur === 'investigators' ? 'active' : '' ?>">
                        Investigators
                    </a>
                </li>
            </ul>

            <div class="navbar-user-actions">
                <?php if ($is_logged_in): ?>
                    <div class="user-profile-badge">
                        <div class="user-details">
                            <span class="user-name"><?= e($user_name) ?></span>
                            <?php if ($rank || $badge_number): ?>
                                <span class="user-rank"><?= e($rank) ?><?= ($rank && $badge_number) ? ' &bull; ' : '' ?><?= e($badge_number) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= $base ?>logout.php" class="btn btn-logout" id="logoutBtn" title="Sign out from GPD Terminal">
                         Logout
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>register.php" class="btn btn-secondary btn-sm" id="navRegisterBtn">
                        Register
                    </a>
                    <a href="<?= $base ?>login.php" class="btn btn-primary btn-sm" id="navLoginBtn">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
