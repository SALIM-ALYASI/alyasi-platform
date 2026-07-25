'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('admin-login-form');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    const submitButton = document.getElementById('login-submit');

    if (passwordInput && passwordToggle) {
        passwordToggle.addEventListener('click', () => {
            const passwordIsHidden = passwordInput.type === 'password';
            const icon = passwordToggle.querySelector('i');

            passwordInput.type = passwordIsHidden ? 'text' : 'password';

            passwordToggle.setAttribute(
                'aria-pressed',
                passwordIsHidden ? 'true' : 'false'
            );

            passwordToggle.setAttribute(
                'aria-label',
                passwordIsHidden
                    ? 'إخفاء كلمة المرور'
                    : 'إظهار كلمة المرور'
            );

            if (icon) {
                icon.classList.toggle('fa-eye', !passwordIsHidden);
                icon.classList.toggle('fa-eye-slash', passwordIsHidden);
            }
        });
    }

    if (form && submitButton) {
        form.addEventListener('submit', () => {
            submitButton.disabled = true;

            submitButton.innerHTML = `
                <span>جارٍ تسجيل الدخول...</span>
                <i class="fa-solid fa-spinner fa-spin"></i>
            `;
        });
    }
});