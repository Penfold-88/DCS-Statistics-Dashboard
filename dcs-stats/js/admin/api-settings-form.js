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
                    alert(window.DCS_ADMIN_API_SETTINGS_CONFIG?.enterHostFirst || 'Enter the API host first.');
                    return;
                }

                actionInput.value = 'test';
                form.submit();
            });
        }
    });
