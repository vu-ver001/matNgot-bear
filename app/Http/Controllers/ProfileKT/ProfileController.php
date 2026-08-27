<?php

namespace App\Http\Controllers\ProfileKT;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileKT\ProfileUpdateRequest;
use App\Models\EmailChangeCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('ProfileKT.index', [
            'user' => $request->user(),
            'emailChangeRequest' => EmailChangeCode::query()
                ->where('user_id', $request->user()->id)
                ->first(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

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

        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');
            $profileData['avatar'] = $newAvatar;
        }

        $profileHasChanges = collect($profileData)->contains(
            fn (mixed $value, string $field): bool => $user->getAttribute($field) !== $value,
        );

        $user->fill($profileData);

        if (! $profileHasChanges) {
            return Redirect::route('profile.edit')
                ->with('status', 'profile-no-changes')
                ->with('profile-editing', true);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        try {
            $user->save();
        } catch (\Throwable $exception) {
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        if ($newAvatar && $oldAvatar && str_starts_with($oldAvatar, 'avatars/')) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
