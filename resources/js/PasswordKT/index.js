const setupPasswordPage = () => {
    const page = document.querySelector('[data-password-page]');

    if (!page) {
        return;
    }

    const passwordInput = page.querySelector('[data-new-password]');
    const strength = page.querySelector('[data-password-strength]');
    const strengthLabel = page.querySelector('[data-password-strength-label]');
    const requirementChecks = {
        length: (value) => value.length >= 8,
        case: (value) => /[a-z]/.test(value) && /[A-Z]/.test(value),
        number: (value) => /\d/.test(value),
        symbol: (value) => /[^A-Za-z0-9]/.test(value),
    };

    const updateStrength = () => {
        const value = passwordInput?.value ?? '';
        const results = Object.entries(requirementChecks).map(([name, check]) => {
            const isMet = check(value);

            page.querySelector(`[data-password-requirement="${name}"]`)?.classList.toggle('is-met', isMet);

            return isMet;
        });
        const score = results.filter(Boolean).length;
        const level = value === '' ? 'empty' : ['weak', 'weak', 'medium', 'good', 'strong'][score];
        const label = value === '' ? 'Chưa nhập' : ['Yếu', 'Yếu', 'Trung bình', 'Khá', 'Mạnh'][score];

        if (strength) {
            strength.dataset.level = level;
        }

        if (strengthLabel) {
            strengthLabel.textContent = label;
        }
    };

    passwordInput?.addEventListener('input', updateStrength);
    updateStrength();

    const changePasswordForm = page.querySelector('[data-change-password-form]');
    const changePasswordButton = page.querySelector('[data-password-submit]');
    const changePasswordLabel = page.querySelector('[data-password-submit-label]');

    changePasswordForm?.addEventListener('submit', () => {
        if (!changePasswordForm.checkValidity() || changePasswordForm.dataset.submitting === 'true') {
            return;
        }

        changePasswordForm.dataset.submitting = 'true';
        changePasswordButton?.setAttribute('disabled', 'disabled');

        if (changePasswordLabel) {
            changePasswordLabel.textContent = 'Đang cập nhật...';
        }
    });

    const modal = page.querySelector('[data-password-reset-modal]');
    const openButtons = page.querySelectorAll('[data-password-reset-open]');
    const closeButtons = page.querySelectorAll('[data-password-reset-close]');
    let previousFocus = null;

    const focusFirstModalControl = () => {
        window.requestAnimationFrame(() => {
            modal?.querySelector('input:not([disabled]), button:not([disabled])')?.focus();
        });
    };

    const openModal = () => {
        if (!modal) {
            return;
        }

        previousFocus = document.activeElement;
        modal.hidden = false;
        document.body.classList.add('has-password-modal');
        focusFirstModalControl();
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('has-password-modal');
        previousFocus?.focus?.();
    };

    openButtons.forEach((button) => button.addEventListener('click', openModal));
    closeButtons.forEach((button) => button.addEventListener('click', closeModal));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    if (page.dataset.resetModalOpen === 'true') {
        openModal();
    }

    const resetForm = modal?.querySelector('[data-password-reset-flow]');

    resetForm?.addEventListener('submit', () => {
        const submitButton = resetForm.querySelector('button[type="submit"]');

        if (!resetForm.checkValidity() || submitButton?.disabled) {
            return;
        }

        submitButton.disabled = true;
        submitButton.querySelector('span:last-child').textContent = 'Đang đặt lại...';
    });

    const toast = page.querySelector('[data-password-toast]');
    const closeToast = () => toast?.remove();

    page.querySelector('[data-password-toast-close]')?.addEventListener('click', closeToast);

    if (toast) {
        window.setTimeout(closeToast, 4000);
    }
};

document.addEventListener('DOMContentLoaded', setupPasswordPage);
