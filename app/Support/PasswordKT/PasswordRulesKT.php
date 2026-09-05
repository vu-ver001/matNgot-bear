<?php

namespace App\Support\PasswordKT;

use Illuminate\Validation\Rules\Password;

class PasswordRulesKT
{
    /**
     * Quy tắc kiểm tra mật khẩu chuẩn hóa cho toàn hệ thống Mật Ngọt Bear (KT).
     * - Tối thiểu 8 ký tự
     * - Chữ hoa và chữ thường
     * - Chữ số (0–9)
     * - Ký tự đặc biệt (!@#$%^&*)
     */
    public static function rule(): Password
    {
        return Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
    }

    /**
     * Mảng thông báo lỗi tiếng Việt đồng bộ cho trường mật khẩu.
     */
    public static function messages(string $field = 'password', bool $isNew = false): array
    {
        return [
            "{$field}.required" => $isNew ? 'Vui lòng nhập mật khẩu mới.' : 'Vui lòng nhập mật khẩu.',
            "{$field}.confirmed" => 'Xác nhận mật khẩu không khớp.',
            "{$field}.min" => 'Mật khẩu phải có ít nhất 8 ký tự.',
            "{$field}.letters" => 'Mật khẩu phải có ít nhất một chữ cái.',
            "{$field}.mixed" => 'Mật khẩu phải kết hợp chữ hoa và chữ thường.',
            "{$field}.numbers" => 'Mật khẩu phải bao gồm ít nhất một chữ số (0–9).',
            "{$field}.symbols" => 'Mật khẩu phải bao gồm ký tự đặc biệt (!@#$%^&*).',
        ];
    }
}
