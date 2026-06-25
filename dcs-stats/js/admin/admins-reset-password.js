    function showResetPasswordModal(adminId, adminName) {
        document.getElementById('resetAdminId').value = adminId;
        document.getElementById('resetAdminName').textContent = adminName;
        document.getElementById('resetPasswordModal').classList.add('active');
    }

    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.remove('active');
        document.getElementById('new_password').value = '';
    }

    document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResetPasswordModal();
        }
    });
