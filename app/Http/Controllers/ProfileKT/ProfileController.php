<?php

namespace App\Http\Controllers\ProfileKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileKT\ProfileUpdateRequest;
use App\Models\EmailChangeCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

// Chức năng: Quản lý thông tin cá nhân của người dùng (ProfileKT)
class ProfileController extends Controller
{
    /**
     * [Giao diện] Hiển thị trang hồ sơ cá nhân (ProfileKT/index.blade.php).
     */
    public function edit(Request $request): View
    {
        return view('ProfileKT.index', [
            'user' => $request->user(),
            // Kiểm tra xem người dùng có yêu cầu đổi email nào đang chờ xác nhận không
            'emailChangeRequest' => EmailChangeCode::query()
                ->where('user_id', $request->user()->id)
                ->first(),
        ]);
    }

    /**
     * Cập nhật thông tin cá nhân (Họ tên, SĐT, Địa chỉ, Avatar).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Chặn người dùng tự ý đổi email trực tiếp qua form update mà không xác minh OTP
        $submittedEmail = mb_strtolower(trim((string) $request->input('email')));
        $hasUnconfirmedEmail = EmailChangeCode::query()
            ->where('user_id', $user->id)
            ->exists()
            || ($request->filled('email') && $submittedEmail !== mb_strtolower($user->email));

        if ($hasUnconfirmedEmail) {
            return Redirect::route('profile.edit')
                ->withErrors([
                    'email' => 'Email mới chưa được xác nhận. Vui lòng nhập mã hoặc hủy đổi email trước khi lưu.',
                ])
                ->with('profile-email-locked', true)
                ->withInput($request->except('email'));
        }

        $profileData = $request->safe()->except('avatar');
        $oldAvatar = $user->avatar;
        $newAvatar = null;

        // Xử lý upload ảnh đại diện mới vào thư mục storage/app/public/avatars
        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');
            $profileData['avatar'] = $newAvatar;
        }

        // Kiểm tra xem có trường dữ liệu nào thực sự thay đổi không
        $profileHasChanges = collect($profileData)->contains(
            fn (mixed $value, string $field): bool => $user->getAttribute($field) !== $value,
        );

        $user->fill($profileData);

        // Nếu không có gì thay đổi thì thông báo cho người dùng biết
        if (! $profileHasChanges) {
            return Redirect::route('profile.edit')
                ->with('status', 'profile-no-changes')
                ->with('profile-editing', true);
        }

        try {
            $user->save();
        } catch (\Throwable $exception) {
            // Xóa ảnh mới tải lên nếu quá trình lưu DB thất bại
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        // Xóa ảnh avatar cũ khỏi bộ nhớ nếu đã đổi ảnh mới thành công
        if ($newAvatar && $oldAvatar && str_starts_with($oldAvatar, 'avatars/')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}
