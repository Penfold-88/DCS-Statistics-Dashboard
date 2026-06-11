<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.api-form');
        if (!form) return;

        const actionInput = form.querySelector('input[name="action"]');
        const testBtn = document.querySelector('button[value="test"]');

        if (testBtn) {
            testBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const apiHostInput = document.getElementById('api_host');
                if (!apiHostInput.value.trim()) {
                    alert(<?= json_encode(dcs_t('admin.api.enter_host_first')) ?>);
                    return;
                }

                actionInput.value = 'test';
                form.submit();
            });
        }
    });
</script>
