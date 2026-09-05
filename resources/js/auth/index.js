import { disablePlaceholderLinks } from './sharedKT/placeholder-link.js';
import { setupPasswordToggles } from './sharedKT/password-toggle.js';
import { initAllPasswordRules } from './sharedKT/password-rules.js';

const setupRegisterFlow = () => {
    const form = document.querySelector('[data-register-flow]');

    if (!form) {
        return;
    }

    const stepNames = ['email', 'otp', 'details'];
    const steps = [...form.querySelectorAll('[data-register-step]')];
    const progressItems = [...document.querySelectorAll('[data-register-progress-item]')];
    const emailInput = form.querySelector('#register_email');
    const emailMessage = form.querySelector('[data-register-email-message]');
    const otpInputs = [...form.querySelectorAll('[data-otp-input]')];
    const otpMessage = form.querySelector('[data-otp-message]');
    const sendCodeButton = form.querySelector('[data-register-next="otp"]');
    const verifyCodeButton = form.querySelector('[data-register-next="details"]');
    const resendCodeButton = form.querySelector('[data-register-resend]');
    const countdown = form.querySelector('[data-register-countdown]');
    const countdownValue = form.querySelector('[data-register-countdown-value]');
    let countdownTimer = null;
    let countdownRemaining = 0;
    let currentStep = stepNames.includes(form.dataset.initialStep) ? form.dataset.initialStep : 'email';

    const setMessage = (element, message = '', isSuccess = false) => {
        if (!element) {
            return;
        }

        element.textContent = message;
        element.hidden = message === '';
        element.classList.toggle('is-success', isSuccess);
    };

    const requestJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify(payload),
        });
        const body = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = Object.values(body.errors ?? {}).flat()[0];
            throw new Error(validationMessage ?? body.message ?? 'Không thể xử lý yêu cầu. Vui lòng thử lại.');
        }

        return body;
    };

    const withPendingState = async (button, callback) => {
        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        try {
            await callback();
        } finally {
            if (button !== resendCodeButton || countdownRemaining <= 0) {
                button.disabled = false;
            }
            button.removeAttribute('aria-busy');
        }
    };

    const renderCountdown = () => {
        if (!countdownValue) {
            return;
        }

        const minutes = Math.floor(countdownRemaining / 60);
        const seconds = countdownRemaining % 60;

        countdownValue.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const stopCountdown = (hide = false) => {
        if (countdownTimer !== null) {
            window.clearInterval(countdownTimer);
            countdownTimer = null;
        }

        countdownRemaining = 0;
        resendCodeButton?.removeAttribute('disabled');

        if (countdown) {
            countdown.hidden = hide;
        }
    };

    const startCountdown = (seconds = 60) => {
        stopCountdown();
        countdownRemaining = Math.max(0, Number.parseInt(seconds, 10) || 60);

        if (countdown) {
            countdown.hidden = false;
            countdown.classList.remove('is-expired');
        }

        if (resendCodeButton) {
            resendCodeButton.disabled = true;
        }

        renderCountdown();

        countdownTimer = window.setInterval(() => {
            countdownRemaining -= 1;
            renderCountdown();

            if (countdownRemaining <= 0) {
                stopCountdown();
                countdown?.classList.add('is-expired');
                setMessage(otpMessage, 'Mã xác nhận đã hết hạn. Bạn hãy gửi lại mã mới.');
            }
        }, 1000);
    };

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
                // Luôn bật trường email để Laravel nhận được email khi gửi biểu mẫu cuối cùng.
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
                form.querySelector('#full_name')?.focus();
            } else {
                emailInput?.focus();
            }
        }
    };

    const openOtpStep = async () => {
        if (!emailInput?.reportValidity()) {
            return;
        }

        setMessage(emailMessage);

        await withPendingState(sendCodeButton, async () => {
            try {
                const result = await requestJson(form.dataset.sendCodeUrl, {
                    email: emailInput.value,
                });

                showStep('otp');
                startCountdown(result.expires_in);
                setMessage(otpMessage, result.message, true);
            } catch (error) {
                setMessage(emailMessage, error.message);
            }
        });
    };

    const openDetailsStep = async () => {
        const otp = otpInputs.map((input) => input.value).join('');

        if (!/^\d{6}$/.test(otp)) {
            setMessage(otpMessage, 'Vui lòng nhập đủ 6 chữ số để xem bước tiếp theo.');
            otpInputs.find((input) => !input.value)?.focus();
            return;
        }

        setMessage(otpMessage);

        await withPendingState(verifyCodeButton, async () => {
            try {
                await requestJson(form.dataset.verifyCodeUrl, {
                    email: emailInput.value,
                    code: otp,
                });

                stopCountdown(true);
                showStep('details');
            } catch (error) {
                setMessage(otpMessage, error.message);
            }
        });
    };

    form.querySelectorAll('[data-register-next]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.dataset.registerNext === 'otp') {
                await openOtpStep();
            } else {
                await openDetailsStep();
            }
        });
    });

    form.querySelectorAll('[data-register-back]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.registerBack === 'email') {
                stopCountdown(true);
            }

            showStep(button.dataset.registerBack);
        });
    });

    resendCodeButton?.addEventListener('click', async () => {
        setMessage(otpMessage);

        await withPendingState(resendCodeButton, async () => {
            try {
                const result = await requestJson(form.dataset.sendCodeUrl, {
                    email: emailInput.value,
                });

                otpInputs.forEach((input) => {
                    input.value = '';
                });
                startCountdown(result.expires_in);
                setMessage(otpMessage, result.message, true);
                otpInputs[0]?.focus();
            } catch (error) {
                setMessage(otpMessage, error.message);
            }
        });
    });

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            setMessage(otpMessage);

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
        currentStep === 'email' ? void openOtpStep() : void openDetailsStep();
    });

    // Không tự lấy tiêu điểm khi trang vừa mở vì trên màn hình hẹp,
    // trình duyệt có thể cuộn sang phải và che mất phần đầu của khối giới thiệu.
    showStep(currentStep, false);
};

const setupPasswordResetFlow = () => {
    const form = document.querySelector('[data-password-reset-flow]');

    if (!form) {
        return;
    }

    const stepNames = ['email', 'otp', 'password'];
    const steps = [...form.querySelectorAll('[data-password-reset-step]')];
    const flowContainer = form.closest('[data-password-reset-container]') ?? document;
    const progressItems = [...flowContainer.querySelectorAll('[data-password-reset-progress-item]')];
    const emailInput = form.querySelector('[data-password-reset-email-input]');
    const emailMessage = form.querySelector('[data-password-reset-email-message]');
    const otpInputs = [...form.querySelectorAll('[data-password-reset-otp-input]')];
    const otpMessage = form.querySelector('[data-password-reset-otp-message]');
    const sendCodeButton = form.querySelector('[data-password-reset-next="otp"]');
    const verifyCodeButton = form.querySelector('[data-password-reset-next="password"]');
    const resendCodeButton = form.querySelector('[data-password-reset-resend]');
    const countdown = form.querySelector('[data-password-reset-countdown]');
    const countdownValue = form.querySelector('[data-password-reset-countdown-value]');
    let countdownTimer = null;
    let countdownRemaining = 0;
    let currentStep = stepNames.includes(form.dataset.initialStep) ? form.dataset.initialStep : 'email';

    const setMessage = (element, message = '', isSuccess = false) => {
        if (!element) {
            return;
        }

        element.textContent = message;
        element.hidden = message === '';
        element.classList.toggle('is-success', isSuccess);
    };

    const requestJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify(payload),
        });
        const body = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = Object.values(body.errors ?? {}).flat()[0];
            throw new Error(validationMessage ?? body.message ?? 'Không thể xử lý yêu cầu. Vui lòng thử lại.');
        }

        return body;
    };

    const withPendingState = async (button, callback) => {
        if (!button) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        try {
            await callback();
        } finally {
            if (button !== resendCodeButton || countdownRemaining <= 0) {
                button.disabled = false;
            }
            button.removeAttribute('aria-busy');
        }
    };

    const renderCountdown = () => {
        if (!countdownValue) {
            return;
        }

        const minutes = Math.floor(countdownRemaining / 60);
        const seconds = countdownRemaining % 60;

        countdownValue.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const stopCountdown = (hide = false) => {
        if (countdownTimer !== null) {
            window.clearInterval(countdownTimer);
            countdownTimer = null;
        }

        countdownRemaining = 0;
        resendCodeButton?.removeAttribute('disabled');

        if (countdown) {
            countdown.hidden = hide;
        }
    };

    const startCountdown = (seconds = 60) => {
        stopCountdown();
        countdownRemaining = Math.max(0, Number.parseInt(seconds, 10) || 60);

        if (countdown) {
            countdown.hidden = false;
            countdown.classList.remove('is-expired');
        }

        if (resendCodeButton) {
            resendCodeButton.disabled = true;
        }

        renderCountdown();

        countdownTimer = window.setInterval(() => {
            countdownRemaining -= 1;
            renderCountdown();

            if (countdownRemaining <= 0) {
                stopCountdown();
                countdown?.classList.add('is-expired');
                setMessage(otpMessage, 'Mã xác nhận đã hết hạn. Bạn hãy gửi lại mã mới.');
            }
        }, 1000);
    };

    const updateEmailLabels = () => {
        form.querySelectorAll('[data-password-reset-email-value]').forEach((label) => {
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
            const isActive = step.dataset.passwordResetStep === stepName;

            step.hidden = !isActive;
            step.querySelectorAll('input, button').forEach((control) => {
                // Trường email luôn được bật để có mặt trong biểu mẫu đặt lại mật khẩu.
                if (control === emailInput) {
                    return;
                }

                control.disabled = !isActive;
            });
        });
        progressItems.forEach((item) => {
            const itemIndex = stepNames.indexOf(item.dataset.passwordResetProgressItem);

            item.classList.toggle('is-active', itemIndex === activeIndex);
            item.classList.toggle('is-complete', itemIndex < activeIndex);
        });

        updateEmailLabels();

        if (!shouldFocus) {
            return;
        }

        if (stepName === 'otp') {
            otpInputs[0]?.focus();
        } else if (stepName === 'password') {
            form.querySelector('[data-password-reset-new-password]')?.focus();
        } else {
            emailInput?.focus();
        }
    };

    const sendCode = async () => {
        if (!emailInput?.reportValidity()) {
            return;
        }

        setMessage(emailMessage);

        await withPendingState(sendCodeButton, async () => {
            try {
                const result = await requestJson(form.dataset.sendCodeUrl, {
                    email: emailInput.value,
                });

                showStep('otp');
                startCountdown(result.expires_in);
                setMessage(otpMessage, result.message, true);
            } catch (error) {
                setMessage(emailMessage, error.message);
            }
        });
    };

    const verifyCode = async () => {
        const otp = otpInputs.map((input) => input.value).join('');

        if (!/^\d{6}$/.test(otp)) {
            setMessage(otpMessage, 'Vui lòng nhập đủ 6 chữ số.');
            otpInputs.find((input) => !input.value)?.focus();
            return;
        }

        setMessage(otpMessage);

        await withPendingState(verifyCodeButton, async () => {
            try {
                await requestJson(form.dataset.verifyCodeUrl, {
                    email: emailInput.value,
                    code: otp,
                });

                stopCountdown(true);
                showStep('password');
            } catch (error) {
                setMessage(otpMessage, error.message);
            }
        });
    };

    sendCodeButton?.addEventListener('click', sendCode);
    verifyCodeButton?.addEventListener('click', verifyCode);

    form.querySelectorAll('[data-password-reset-back]').forEach((button) => {
        button.addEventListener('click', () => {
            stopCountdown(true);
            showStep(button.dataset.passwordResetBack);
        });
    });

    resendCodeButton?.addEventListener('click', async () => {
        setMessage(otpMessage);

        await withPendingState(resendCodeButton, async () => {
            try {
                const result = await requestJson(form.dataset.sendCodeUrl, {
                    email: emailInput.value,
                });

                otpInputs.forEach((input) => {
                    input.value = '';
                });
                startCountdown(result.expires_in);
                setMessage(otpMessage, result.message, true);
                otpInputs[0]?.focus();
            } catch (error) {
                setMessage(otpMessage, error.message);
            }
        });
    });

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            setMessage(otpMessage);

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
        if (currentStep === 'password') {
            return;
        }

        event.preventDefault();
        currentStep === 'email' ? void sendCode() : void verifyCode();
    });

    // Không tự lấy tiêu điểm khi trang vừa mở để tránh trình duyệt tự cuộn ngang.
    showStep(currentStep, false);
};

document.addEventListener('DOMContentLoaded', () => {
    setupPasswordToggles();
    disablePlaceholderLinks();
    setupRegisterFlow();
    setupPasswordResetFlow();
    initAllPasswordRules();
});
