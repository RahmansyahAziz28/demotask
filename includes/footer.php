        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

document.addEventListener('DOMContentLoaded', function () {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity 0.5s';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }
});

function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this record? This action cannot be undone.');
}
</script>
</body>
</html>
