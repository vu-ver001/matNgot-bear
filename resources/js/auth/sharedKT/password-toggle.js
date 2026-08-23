export const setupPasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const shouldShowPassword = input.type === 'password';

            input.type = shouldShowPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(shouldShowPassword));
            button.setAttribute('aria-label', shouldShowPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            button.querySelector('[data-icon-show]')?.classList.toggle('hidden', shouldShowPassword);
            button.querySelector('[data-icon-hide]')?.classList.toggle('hidden', !shouldShowPassword);
        });
    });
};
