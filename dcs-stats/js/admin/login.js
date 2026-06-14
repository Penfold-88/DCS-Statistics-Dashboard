        document.getElementById('username').focus();

        const passwordInput = document.getElementById('password');
        const togglePassword = document.createElement('span');
        togglePassword.innerHTML = '👁';
        togglePassword.style.position = 'absolute';
        togglePassword.style.right = '10px';
        togglePassword.style.top = '50%';
        togglePassword.style.transform = 'translateY(-50%)';
        togglePassword.style.cursor = 'pointer';
        togglePassword.style.userSelect = 'none';

        passwordInput.parentElement.style.position = 'relative';
        passwordInput.parentElement.appendChild(togglePassword);

        let showPassword = false;
        togglePassword.addEventListener('click', () => {
            showPassword = !showPassword;
            passwordInput.type = showPassword ? 'text' : 'password';
            togglePassword.innerHTML = showPassword ? '👁‍🗨' : '👁';
        });
