<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;

class UserService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = User::query()->withCount('orders');

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(15);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        if ($user->id === auth()->id()) {
            unset($data['role']);
        }

        $attributes = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role' => $data['role'],
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        return $user->update($attributes);
    }

    public function delete(User $user): ?RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        if ($user->role === 'ADMIN') {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa tài khoản quản trị viên.');
        }

        if ($user->orders()->exists()) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa người dùng đã có đơn hàng.');
        }

        $user->delete();

        return null;
    }

    public function toggleStatus(User $user): ?RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Không thể khóa/mở khóa tài khoản đang đăng nhập.');
        }

        $newStatus = $user->status === 'ACTIVE' ? 'BLOCKED' : 'ACTIVE';
        $user->update(['status' => $newStatus]);

        return null;
    }
}
