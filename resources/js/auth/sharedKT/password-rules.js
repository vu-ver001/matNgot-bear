export const requirementChecks = {
    length: (value) => value.length >= 8,
    case: (value) => /[a-z]/.test(value) && /[A-Z]/.test(value),
    number: (value) => /\d/.test(value),
    symbol: (value) => /[^A-Za-z0-9]/.test(value),
};

// Danh sách từ điển thông dụng / từ khóa hacker hay dò
const COMMON_DICTIONARY_WORDS = [
    'password', 'admin', 'administrator', 'root', 'user', 'guest',
    'matkhau', 'matngot', 'bear', 'welcome', 'login', 'security',
    'iloveyou', 'letmein', 'qwerty', 'asdfgh', 'zxcvbn', '123456',
    'default', 'access', 'secret', 'master', 'testing', 'sample'
];

// Danh sách chuỗi liên tiếp (bàn phím, số, bảng chữ cái)
const SEQUENTIAL_PATTERNS = [
    '0123456789',
    '9876543210',
    'abcdefghijklmnopqrstuvwxyz',
    'zyxwvutsrqponmlkjihgfedcba',
    'qwertyuiop',
    'asdfghjkl',
    'zxcvbnm',
];

/**
 * Thuật toán đo độ mạnh mật khẩu thông minh (Entropy & Pattern Detection):
 * - Đánh giá độ dài thực tế và độ đa dạng bảng ký tự
 * - Trừ điểm các mẫu dễ đoán: từ điển thông dụng, chuỗi liên tiếp, ký tự lặp
 */
export const calculatePasswordStrength = (value) => {
    if (!value || value.length === 0) {
        return {
            score: 0,
            level: 'empty',
            label: 'Chưa nhập',
        };
    }

    const lower = value.toLowerCase();
    let score = 0;

    // 1. Điểm cơ bản theo độ dài
    if (value.length >= 8) score += 1;
    if (value.length >= 10) score += 1;
    if (value.length >= 14) score += 1;
    if (value.length >= 18) score += 1;

    // 2. Điểm theo độ đa dạng các nhóm ký tự
    const hasLower = /[a-z]/.test(value);
    const hasUpper = /[A-Z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    const varietyCount = [hasLower, hasUpper, hasNumber, hasSymbol].filter(Boolean).length;
    if (varietyCount >= 3) score += 1;
    if (varietyCount === 4) score += 1;

    // 3. Trừ điểm các mẫu dễ đoán (Penalties)

    // a) Chứa từ ngữ từ điển thông dụng
    for (const word of COMMON_DICTIONARY_WORDS) {
        if (lower.includes(word)) {
            score -= (word.length >= 6 ? 2 : 1);
            break;
        }
    }

    // b) Ký tự lặp lại liên tiếp 3 lần trở lên (ví dụ: 'aaa', '111', '!!!')
    if (/(.)\1{2,}/i.test(value)) {
        score -= 1;
    }

    // c) Chuỗi ký tự liên tiếp 3 ký tự trở lên (ví dụ: '123', 'abc', 'qwe')
    let hasSequence = false;
    for (const pattern of SEQUENTIAL_PATTERNS) {
        for (let i = 0; i <= pattern.length - 3; i++) {
            const sub = pattern.slice(i, i + 3);
            if (lower.includes(sub)) {
                hasSequence = true;
                break;
            }
        }
        if (hasSequence) break;
    }
    if (hasSequence) {
        score -= 1;
    }

    // d) Cấu trúc phản xạ thường gặp: Viết hoa đầu từ + chữ thường + số + ký tự đặc biệt cuối (ví dụ: Password123!)
    if (/^[A-Z][a-z]+\d+[^A-Za-z0-9]$/.test(value)) {
        score -= 1;
    }

    // Chuẩn hóa điểm
    if (value.length < 8) {
        score = 1;
    } else {
        score = Math.max(1, Math.min(4, score));
    }

    const levels = ['empty', 'weak', 'medium', 'good', 'strong'];
    const labels = ['Chưa nhập', 'Yếu', 'Trung bình', 'Khá', 'Mạnh'];

    return {
        score,
        level: levels[score],
        label: labels[score],
    };
};

/**
 * Liên kết ô nhập mật khẩu với khối hiển thị tiêu chí & thanh đo độ mạnh mật khẩu.
 */
export const bindPasswordRules = (input, rulesContainer) => {
    if (!input || !rulesContainer) {
        return;
    }

    const strength = rulesContainer.querySelector('[data-password-strength]');
    const strengthLabel = rulesContainer.querySelector('[data-password-strength-label]');

    const update = () => {
        const value = input.value ?? '';

        // Cập nhật 4 tiêu chuẩn bắt buộc (hiển thị tick xanh)
        Object.entries(requirementChecks).forEach(([name, check]) => {
            const isMet = check(value);
            rulesContainer.querySelector(`[data-password-requirement="${name}"]`)?.classList.toggle('is-met', isMet);
        });

        // Đo độ mạnh thông minh theo entropy và nhận diện mẫu dễ đoán
        const { level, label } = calculatePasswordStrength(value);

        if (strength) {
            strength.dataset.level = level;
        }

        if (strengthLabel) {
            strengthLabel.textContent = label;
        }
    };

    input.addEventListener('input', update);
    update();
};

/**
 * Tự động tìm và liên kết tất cả các khối [data-password-rules-box] trong phạm vi container.
 */
export const initAllPasswordRules = (container = document) => {
    container.querySelectorAll('[data-password-rules-box]').forEach((rulesBox) => {
        const targetId = rulesBox.dataset.targetInput;
        let targetInput = null;

        if (targetId) {
            targetInput = container.querySelector(`#${targetId}`) || document.getElementById(targetId);
        }

        if (!targetInput) {
            targetInput = rulesBox.closest('.auth-field')?.querySelector('input[type="password"]');
        }

        if (targetInput) {
            bindPasswordRules(targetInput, rulesBox);
        }
    });
};
