const setupPasswordToggles = () => {
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

const disablePlaceholderLinks = () => {
    document.querySelectorAll('[data-placeholder-link]').forEach((link) => {
        link.addEventListener('click', (event) => event.preventDefault());
    });
};

const setupRegisterFlow = () => {
    const form = document.querySelector('[data-register-flow]');

    if (!form) {
        return;
    }

    const stepNames = ['email', 'otp', 'details'];
    const steps = [...form.querySelectorAll('[data-register-step]')];
    const progressItems = [...document.querySelectorAll('[data-register-progress-item]')];
    const emailInput = form.querySelector('#register_email');
    const otpInputs = [...form.querySelectorAll('[data-otp-input]')];
    const otpMessage = form.querySelector('[data-otp-message]');
    let currentStep = stepNames.includes(form.dataset.initialStep) ? form.dataset.initialStep : 'email';

    const updateEmailLabels = () => {
        form.querySelectorAll('[data-register-email-value]').forEach((label) => {
            label.textContent = emailInput?.value.trim() || 'email của bạn';
        });
    };

    const showStep = (stepName, shouldFocus = true) => {
        const activeIndex = stepNames.indexOf(stepName);

        if (activeIndex === -1) {
            return;
        }

        currentStep = stepName;
        steps.forEach((step) => {
            const isActive = step.dataset.registerStep === stepName;

            step.hidden = !isActive;
            step.querySelectorAll('input, button, select, textarea').forEach((control) => {
                // Keep the email enabled so Laravel receives it with the final form submit.
                if (control === emailInput) {
                    return;
                }

                control.disabled = !isActive;
            });
        });
        progressItems.forEach((item) => {
            const itemIndex = stepNames.indexOf(item.dataset.registerProgressItem);
            item.classList.toggle('is-active', itemIndex === activeIndex);
            item.classList.toggle('is-complete', itemIndex < activeIndex);
        });

        updateEmailLabels();

        if (shouldFocus) {
            if (stepName === 'otp') {
                otpInputs[0]?.focus();
            } else if (stepName === 'details') {
                form.querySelector('#name')?.focus();
            } else {
                emailInput?.focus();
            }
        }
    };

    const openOtpStep = () => {
        if (!emailInput?.reportValidity()) {
            return;
        }

        showStep('otp');
    };

    const openDetailsStep = () => {
        const otp = otpInputs.map((input) => input.value).join('');

        if (!/^\d{6}$/.test(otp)) {
            otpMessage.textContent = 'Vui lòng nhập đủ 6 chữ số để xem bước tiếp theo.';
            otpInputs.find((input) => !input.value)?.focus();
            return;
        }

        otpMessage.textContent = '';
        showStep('details');
    };

    form.querySelectorAll('[data-register-next]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.registerNext === 'otp') {
                openOtpStep();
            } else {
                openDetailsStep();
            }
        });
    });

    form.querySelectorAll('[data-register-back]').forEach((button) => {
        button.addEventListener('click', () => showStep(button.dataset.registerBack));
    });

    form.querySelector('[data-register-resend]')?.addEventListener('click', () => {
        otpMessage.textContent = 'Chức năng gửi lại mã sẽ hoạt động sau khi kết nối backend.';
    });

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            otpMessage.textContent = '';

            if (input.value) {
                otpInputs[index + 1]?.focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value) {
                otpInputs[index - 1]?.focus();
            }
        });

        input.addEventListener('paste', (event) => {
            const pastedCode = event.clipboardData?.getData('text').replace(/\D/g, '').slice(0, 6);

            if (!pastedCode) {
                return;
            }

            event.preventDefault();
            pastedCode.split('').forEach((digit, digitIndex) => {
                if (otpInputs[digitIndex]) {
                    otpInputs[digitIndex].value = digit;
                }
            });
            otpInputs[Math.min(pastedCode.length, 6) - 1]?.focus();
        });
    });

    form.addEventListener('submit', (event) => {
        if (currentStep === 'details') {
            return;
        }

        event.preventDefault();
        currentStep === 'email' ? openOtpStep() : openDetailsStep();
    });

    // Do not focus on first paint: focusing the form would move a narrow
    // desktop canvas to the right and hide the beginning of the hero panel.
    showStep(currentStep, false);
};

document.addEventListener('DOMContentLoaded', () => {
    setupPasswordToggles();
    disablePlaceholderLinks();
    setupRegisterFlow();
});
