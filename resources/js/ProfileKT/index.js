// Tương tác dùng chung của chức năng Hồ sơ cho Customer, Staff và Admin.
document.querySelectorAll('[data-account-toast]').forEach((toast) => {
    const closeButton = toast.querySelector('[data-account-toast-close]');
    const hideToast = () => toast.classList.remove('is-visible');
    let toastTimer;

    const showToast = (message, type = 'success') => {
        window.clearTimeout(toastTimer);
        toast.querySelector('p').textContent = message;
        toast.querySelector('span').textContent = type === 'error' ? '!' : (type === 'info' ? 'i' : '✓');
        toast.classList.toggle('is-error', type === 'error');
        toast.classList.toggle('is-info', type === 'info');
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.classList.add('is-visible');
        toastTimer = window.setTimeout(hideToast, 4200);
    };

    toast.addEventListener('account-toast:show', (event) => {
        showToast(event.detail.message, event.detail.type);
    });

    if (toast.classList.contains('is-visible')) {
        toastTimer = window.setTimeout(hideToast, 4200);
    }

    closeButton?.addEventListener('click', () => {
        window.clearTimeout(toastTimer);
        hideToast();
    });
});

const avatarInput = document.querySelector('[data-profile-avatar-input]');

if (avatarInput) {
    const avatarPreview = document.querySelector('[data-profile-avatar-preview]');
    const avatarFallback = document.querySelector('[data-profile-avatar-fallback]');
    const avatarEditor = document.querySelector('[data-profile-avatar-editor]');
    const editorFrame = avatarEditor?.querySelector('[data-profile-avatar-editor-frame]');
    const editorImage = avatarEditor?.querySelector('[data-profile-avatar-editor-image]');
    const applyButton = avatarEditor?.querySelector('[data-profile-avatar-editor-apply]');
    const cancelButtons = avatarEditor?.querySelectorAll('[data-profile-avatar-editor-cancel]') || [];
    let sourceUrl;
    let previewUrl;
    let confirmedFile;
    let positionX = 50;
    let positionY = 50;
    let dragState;

    const clamp = (value) => Math.min(100, Math.max(0, value));

    const updateEditorPosition = () => {
        if (editorImage) editorImage.style.objectPosition = `${positionX}% ${positionY}%`;
    };

    const replaceInputFile = (file) => {
        const transfer = new DataTransfer();

        if (file) transfer.items.add(file);

        avatarInput.files = transfer.files;
    };

    const closeEditor = (restoreConfirmedFile = false) => {
        if (!avatarEditor) return;

        avatarEditor.hidden = true;
        document.body.classList.remove('profile-avatar-editor-open');

        if (sourceUrl) {
            URL.revokeObjectURL(sourceUrl);
            sourceUrl = undefined;
        }

        editorImage?.removeAttribute('src');

        if (restoreConfirmedFile) replaceInputFile(confirmedFile);
    };

    const openEditor = (file) => {
        if (!avatarEditor || !editorImage) return;

        if (sourceUrl) URL.revokeObjectURL(sourceUrl);

        sourceUrl = URL.createObjectURL(file);
        positionX = 50;
        positionY = 50;
        updateEditorPosition();
        editorImage.src = sourceUrl;
        avatarEditor.hidden = false;
        document.body.classList.add('profile-avatar-editor-open');
        editorImage.focus();
    };

    avatarInput.addEventListener('change', () => {
        const [file] = avatarInput.files;
        const allowedTypes = ['image/jpeg', 'image/png'];
        const maximumSize = 5 * 1024 * 1024;

        if (!file || !avatarPreview || !allowedTypes.includes(file.type) || file.size > maximumSize) {
            replaceInputFile(confirmedFile);
            return;
        }

        openEditor(file);
    });

    editorImage?.addEventListener('pointerdown', (event) => {
        if (!editorFrame || !editorImage.naturalWidth || !editorImage.naturalHeight) return;

        const frameSize = editorFrame.clientWidth;
        const scale = Math.max(
            frameSize / editorImage.naturalWidth,
            frameSize / editorImage.naturalHeight,
        );

        dragState = {
            pointerId: event.pointerId,
            startX: event.clientX,
            startY: event.clientY,
            positionX,
            positionY,
            overflowX: Math.max(0, editorImage.naturalWidth * scale - frameSize),
            overflowY: Math.max(0, editorImage.naturalHeight * scale - frameSize),
        };

        editorImage.setPointerCapture(event.pointerId);
    });

    editorImage?.addEventListener('pointermove', (event) => {
        if (!dragState || dragState.pointerId !== event.pointerId) return;

        if (dragState.overflowX > 0) {
            positionX = clamp(
                dragState.positionX - ((event.clientX - dragState.startX) / dragState.overflowX) * 100,
            );
        }

        if (dragState.overflowY > 0) {
            positionY = clamp(
                dragState.positionY - ((event.clientY - dragState.startY) / dragState.overflowY) * 100,
            );
        }

        updateEditorPosition();
    });

    const finishDragging = (event) => {
        if (dragState?.pointerId === event.pointerId) dragState = undefined;
    };

    editorImage?.addEventListener('pointerup', finishDragging);
    editorImage?.addEventListener('pointercancel', finishDragging);

    editorImage?.addEventListener('keydown', (event) => {
        const movements = {
            ArrowLeft: [-3, 0],
            ArrowRight: [3, 0],
            ArrowUp: [0, -3],
            ArrowDown: [0, 3],
        };
        const movement = movements[event.key];

        if (!movement) return;

        event.preventDefault();
        positionX = clamp(positionX + movement[0]);
        positionY = clamp(positionY + movement[1]);
        updateEditorPosition();
    });

    applyButton?.addEventListener('click', async () => {
        if (!editorImage?.naturalWidth || !editorImage.naturalHeight || !avatarPreview) return;

        applyButton.disabled = true;

        try {
            const cropSize = Math.min(editorImage.naturalWidth, editorImage.naturalHeight);
            const sourceX = (editorImage.naturalWidth - cropSize) * (positionX / 100);
            const sourceY = (editorImage.naturalHeight - cropSize) * (positionY / 100);
            const outputSize = Math.min(800, cropSize);
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = outputSize;
            canvas.height = outputSize;
            context.drawImage(
                editorImage,
                sourceX,
                sourceY,
                cropSize,
                cropSize,
                0,
                0,
                outputSize,
                outputSize,
            );

            const originalFile = avatarInput.files[0];
            const outputType = originalFile.type === 'image/png' ? 'image/png' : 'image/jpeg';
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, outputType, 0.9));

            if (!blob) throw new Error('Không thể cắt ảnh đã chọn.');

            const extension = outputType === 'image/png' ? 'png' : 'jpg';
            confirmedFile = new File([blob], `avatar-cropped.${extension}`, {
                type: outputType,
                lastModified: Date.now(),
            });
            replaceInputFile(confirmedFile);

            if (previewUrl) URL.revokeObjectURL(previewUrl);

            previewUrl = URL.createObjectURL(confirmedFile);
            avatarPreview.src = previewUrl;
            avatarPreview.style.objectPosition = 'center';
            avatarPreview.classList.remove('hidden');
            avatarFallback?.classList.add('hidden');
            closeEditor();
        } finally {
            applyButton.disabled = false;
        }
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', () => closeEditor(true));
    });

    avatarInput.addEventListener('profile-avatar:reset', () => {
        confirmedFile = undefined;
        closeEditor();

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = undefined;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && avatarEditor && !avatarEditor.hidden) closeEditor(true);
    });

    window.addEventListener('beforeunload', () => {
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        if (previewUrl) URL.revokeObjectURL(previewUrl);
    });
}

const profileEditor = document.querySelector('[data-profile-editor]');

if (profileEditor) {
    const profileScrollKey = 'mat-ngot-bear-profile-scroll-y';
    const editButton = profileEditor.querySelector('[data-profile-edit-button]');
    const cancelButton = profileEditor.querySelector('[data-profile-cancel-edit]');
    const editableFields = profileEditor.querySelectorAll('[data-profile-editable]');
    const editOnlyElements = profileEditor.querySelectorAll('[data-profile-edit-only]');
    const emailInput = profileEditor.querySelector('[data-profile-email-input]');
    const emailAction = profileEditor.querySelector('[data-profile-email-action]');
    const emailVerification = profileEditor.querySelector('[data-profile-email-verification]');
    const pendingEmail = profileEditor.querySelector('[data-profile-pending-email]');
    const emailError = profileEditor.querySelector('[data-profile-email-error]');
    const codeError = profileEditor.querySelector('[data-profile-code-error]');
    const codeInput = document.getElementById('email_change_code');
    const profileToast = profileEditor.querySelector('[data-profile-toast]');
    const profileUpdateForm = document.getElementById('profile-update-form');
    const emailCodeForm = document.getElementById('profile-email-code-form');
    const emailVerifyForm = document.getElementById('profile-email-verify-form');
    const emailCancelForm = document.getElementById('profile-email-cancel-form');
    const profileForms = [
        profileUpdateForm,
        emailCodeForm,
        emailVerifyForm,
        emailCancelForm,
    ].filter(Boolean);
    const avatarPreview = profileEditor.querySelector('[data-profile-avatar-preview]');
    const avatarFallback = profileEditor.querySelector('[data-profile-avatar-fallback]');
    const originalAvatarSrc = avatarPreview?.getAttribute('src');
    const avatarWasHidden = avatarPreview?.classList.contains('hidden');
    const fallbackWasHidden = avatarFallback?.classList.contains('hidden');
    let hasPendingEmailChange = profileEditor.dataset.profileEmailPending === 'true';
    let currentEmail = profileEditor.dataset.profileCurrentEmail;

    try {
        const savedScrollPosition = window.sessionStorage.getItem(profileScrollKey);

        if (savedScrollPosition !== null) {
            window.sessionStorage.removeItem(profileScrollKey);
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => window.scrollTo(0, Number(savedScrollPosition)));
            });
        }
    } catch {
        // Trình duyệt có thể chặn sessionStorage; biểu mẫu vẫn hoạt động bình thường.
    }

    profileUpdateForm?.addEventListener('submit', (event) => {
        const enteredEmail = emailInput?.value.trim().toLowerCase();
        const emailChangeIsIncomplete = hasPendingEmailChange
            || (enteredEmail && enteredEmail !== currentEmail.toLowerCase());

        if (emailChangeIsIncomplete) {
            event.preventDefault();
            emailInput.value = hasPendingEmailChange
                ? emailInput.defaultValue
                : currentEmail;
            resetEmailEditor();
            setFieldError(
                emailError,
                'Email mới chưa được xác nhận. Vui lòng nhập mã hoặc hủy đổi email trước khi lưu.',
            );
            emailVerification.hidden = !hasPendingEmailChange;
            return;
        }

        const profileHasChanges = Array.from(editableFields).some(
            (field) => field.value !== (field.dataset.profileOriginalValue || ''),
        ) || Boolean(avatarInput?.files?.length);

        if (!profileHasChanges) {
            event.preventDefault();
            showProfileToast('Thông tin chưa có thay đổi.', 'info');
            return;
        }

        try {
            window.sessionStorage.setItem(profileScrollKey, String(window.scrollY));
        } catch {
            // Không ảnh hưởng tới việc gửi biểu mẫu nếu sessionStorage bị chặn.
        }
    });

    const showProfileToast = (message, type = 'success') => {
        profileToast?.dispatchEvent(new CustomEvent('account-toast:show', {
            detail: { message, type },
        }));
    };

    const setFieldError = (element, message = '') => {
        if (!element) return;

        element.textContent = message;
        element.hidden = message === '';
    };

    const clearEmailErrors = () => {
        setFieldError(emailError);
        setFieldError(codeError);
    };

    const submitEmailForm = async (event, onSuccess, successType = 'success') => {
        event.preventDefault();
        clearEmailErrors();

        const form = event.currentTarget;
        const submitButton = event.submitter;

        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const result = await response.json();

            if (!response.ok) {
                const errors = result.errors || {};
                const emailErrorMessage = errors.email?.[0] || '';
                const codeErrorMessage = errors.code?.[0] || '';

                setFieldError(emailError, emailErrorMessage);
                setFieldError(codeError, codeErrorMessage);

                if (emailErrorMessage || codeErrorMessage) return;

                throw new Error(result.message || 'Không thể xử lý yêu cầu.');
            }

            onSuccess(result);
            showProfileToast(result.message, successType);
        } catch (error) {
            showProfileToast(error.message || 'Không thể kết nối tới máy chủ.', 'error');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    };

    const resetEmailEditor = () => {
        if (!emailInput || !emailAction) return;

        emailInput.readOnly = true;
        emailInput.setAttribute('aria-readonly', 'true');
        emailAction.type = hasPendingEmailChange ? 'submit' : 'button';
        emailAction.textContent = hasPendingEmailChange ? 'Gửi lại mã' : 'Đổi email';
    };

    const setEditMode = (isEditing) => {
        profileEditor.classList.toggle('is-editing', isEditing);

        editableFields.forEach((field) => {
            field.readOnly = !isEditing;
            field.setAttribute('aria-readonly', isEditing ? 'false' : 'true');
        });

        editOnlyElements.forEach((element) => {
            element.hidden = !isEditing;
        });

        if (emailVerification) {
            emailVerification.hidden = !isEditing || !hasPendingEmailChange;
        }

        if (editButton) editButton.hidden = isEditing;
    };

    editButton?.addEventListener('click', () => setEditMode(true));

    emailAction?.addEventListener('click', (event) => {
        if (emailAction.type === 'submit') return;

        event.preventDefault();
        emailInput.readOnly = false;
        emailInput.setAttribute('aria-readonly', 'false');
        emailAction.type = 'submit';
        emailAction.textContent = 'Gửi mã';
        emailInput.focus();
        emailInput.select();
    });

    emailCodeForm?.addEventListener('submit', (event) => {
        submitEmailForm(event, (result) => {
            hasPendingEmailChange = true;
            profileEditor.dataset.profileEmailPending = 'true';
            emailInput.value = result.email;
            emailInput.defaultValue = result.email;
            emailInput.readOnly = true;
            emailInput.setAttribute('aria-readonly', 'true');
            emailAction.type = 'submit';
            emailAction.textContent = 'Gửi lại mã';
            pendingEmail.textContent = result.email;
            codeInput.value = '';
            emailVerification.hidden = false;
        });
    });

    emailVerifyForm?.addEventListener('submit', (event) => {
        submitEmailForm(event, (result) => {
            currentEmail = result.email;
            hasPendingEmailChange = false;
            profileEditor.dataset.profileCurrentEmail = currentEmail;
            profileEditor.dataset.profileEmailPending = 'false';
            emailInput.value = currentEmail;
            emailInput.defaultValue = currentEmail;
            codeInput.value = '';
            emailVerification.hidden = true;
            resetEmailEditor();

            document.querySelectorAll('[data-account-current-email]').forEach((element) => {
                element.textContent = currentEmail;
            });
        });
    });

    emailCancelForm?.addEventListener('submit', (event) => {
        submitEmailForm(event, () => {
            hasPendingEmailChange = false;
            profileEditor.dataset.profileEmailPending = 'false';
            emailInput.value = currentEmail;
            emailInput.defaultValue = currentEmail;
            codeInput.value = '';
            emailVerification.hidden = true;
            resetEmailEditor();
        }, 'info');
    });

    cancelButton?.addEventListener('click', () => {
        profileForms.forEach((form) => form.reset());
        avatarInput?.dispatchEvent(new CustomEvent('profile-avatar:reset'));

        if (avatarPreview) {
            if (originalAvatarSrc) avatarPreview.src = originalAvatarSrc;
            avatarPreview.classList.toggle('hidden', avatarWasHidden ?? true);
        }

        avatarFallback?.classList.toggle('hidden', fallbackWasHidden ?? false);
        resetEmailEditor();
        setEditMode(false);
    });

    setEditMode(profileEditor.dataset.profileEditingInitially === 'true');
}
