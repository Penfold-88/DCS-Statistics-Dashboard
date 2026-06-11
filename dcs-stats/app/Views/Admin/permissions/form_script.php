<script>
    function updatePermissionUI(checkbox) {
        const permItem = checkbox.closest('.permission-item');
        if (checkbox.checked) {
            permItem.classList.add('enabled');
        } else {
            permItem.classList.remove('enabled');
        }
        updateCounts();
    }

    function updateCounts() {
        const coreChecked = document.querySelectorAll('#core-permissions input[type="checkbox"]:checked').length;
        document.getElementById('core-count').textContent = coreChecked;

        const mgmtChecked = document.querySelectorAll('#mgmt-permissions input[type="checkbox"]:checked').length;
        document.getElementById('mgmt-count').textContent = mgmtChecked;
    }

    function selectAll() {
        if (window.DCS_DEMO_RESTRICTED) return;
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = true;
            updatePermissionUI(cb);
        });
    }

    function selectNone() {
        if (window.DCS_DEMO_RESTRICTED) return;
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = false;
            updatePermissionUI(cb);
        });
    }

    function selectDefault() {
        if (window.DCS_DEMO_RESTRICTED) return;
        const defaults = ['view_dashboard', 'export_data', 'view_logs'];
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
            cb.checked = defaults.includes(cb.value);
            updatePermissionUI(cb);
        });
    }

    window.DCS_DEMO_RESTRICTED = <?= $demoRestricted ? 'true' : 'false' ?>;
    document.addEventListener('DOMContentLoaded', updateCounts);
</script>
