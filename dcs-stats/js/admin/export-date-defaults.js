    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.querySelectorAll('input[name="date_to"]').forEach(input => {
            if (!input.value) input.value = today;
        });

        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        const fromDate = thirtyDaysAgo.toISOString().split('T')[0];
        document.querySelectorAll('input[name="date_from"]').forEach(input => {
            if (!input.value && input.hasAttribute('required')) {
                input.value = fromDate;
            }
        });
    });
