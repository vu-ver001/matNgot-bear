<?php

namespace App\Http\Requests\ProfileKT;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phone = trim((string) $this->input('phone'));
        $address = trim((string) $this->input('address'));

        $this->merge([
            'full_name' => trim((string) $this->input('full_name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => $phone === '' ? null : $phone,
            'address' => $address === '' ? null : $address,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'full_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\pL\s'-]+$/u"],
            'phone' => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'full_name.regex' => 'Họ và tên chỉ được chứa chữ cái, khoảng trắng, dấu nháy đơn hoặc dấu gạch nối.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 150 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 hoặc +84 và có độ dài hợp lệ.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'avatar.uploaded' => 'Không thể tải ảnh lên. Vui lòng kiểm tra dung lượng ảnh và giới hạn upload của PHP.',
            'avatar.image' => 'Ảnh đại diện phải là một file ảnh hợp lệ.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận định dạng JPG, JPEG hoặc PNG.',
            'avatar.max' => 'Ảnh đại diện không được lớn hơn 5MB.',
        ];
    }
}
