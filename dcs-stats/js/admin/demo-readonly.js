document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.admin-main form, .admin-main button, .admin-main input, .admin-main select, .admin-main textarea').forEach(function(element) {
        if (element.closest('.admin-user-menu')) {
            return;
        }
        if (element.matches('.theme-tab, .tab-button, [role="tab"], [data-demo-readonly-nav]')) {
            return;
        }
        if (element.tagName === 'FORM') {
            element.addEventListener('submit', function(event) {
                event.preventDefault();
                alert('Demo mode is enabled. The admin panel is read-only on the public demo.');
            });
            element.classList.add('demo-readonly-lock');
            return;
        }
        element.disabled = true;
    });
});
