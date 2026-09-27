<?php
$base = $base_path ?? '';
$cur  = $current_page ?? 'dashboard';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">
            <span class="logo-main">GOTHAM</span>
            <span class="logo-sub">CRIME RECORDS</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Navigation</div>

        <a href="<?= $base ?>index.php" class="nav-item <?= $cur === 'dashboard' ? 'active' : '' ?>">
            <span>Dashboard</span>
        </a>

        <div class="nav-section-label">Modules</div>

        <a href="<?= $base ?>cases/index.php" class="nav-item <?= $cur === 'cases' ? 'active' : '' ?>">
            <span>Case Files</span>
        </a>

        <a href="<?= $base ?>suspects/index.php" class="nav-item <?= $cur === 'suspects' ? 'active' : '' ?>">
            <span>Suspects</span>
        </a>

        <a href="<?= $base ?>investigators/index.php" class="nav-item <?= $cur === 'investigators' ? 'active' : '' ?>">
            <span>Investigators</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="footer-tagline">Gotham City Police Department</div>
        <div class="footer-sub">Internal Records System v1.0</div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
