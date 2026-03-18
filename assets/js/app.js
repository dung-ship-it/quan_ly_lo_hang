// Toggle sidebar
document.addEventListener('DOMContentLoaded', function () {
    const sidebar   = document.getElementById('sidebar');
    const wrapper   = document.querySelector('.wrapper');
    const toggleBtn = document.getElementById('toggleSidebar');

    // Restore state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar && sidebar.classList.add('collapsed');
        wrapper && wrapper.classList.add('collapsed');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (sidebar) sidebar.classList.toggle('collapsed');
            if (wrapper) wrapper.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar ? sidebar.classList.contains('collapsed') : false);
        });
    }
});
