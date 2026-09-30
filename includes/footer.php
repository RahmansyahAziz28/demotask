        </main>
        <footer class="app-footer">
            <div class="footer-inner">
                <div class="footer-col-left">
                    <span class="footer-badge">&#9733; GPD ARCHIVES</span>
                    <span class="footer-meta">Internal Records System &bull; Authorized Personnel Only</span>
                </div>
                <div class="footer-col-right">
                    <span class="footer-secure">&bull; System Online &bull; Encrypted Session</span>
                </div>
            </div>
        </footer>
    </div>

<script>
function toggleNavMenu() {
    const navGroup = document.getElementById('navbarNavGroup');
    const toggleBtn = document.getElementById('navToggleBtn');
    if (navGroup) {
        navGroup.classList.toggle('nav-open');
    }
    if (toggleBtn) {
        toggleBtn.classList.toggle('open');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => flash.remove(), 4500);
    }
});

function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this record? This action cannot be undone.');
}
</script>
</body>
</html>
