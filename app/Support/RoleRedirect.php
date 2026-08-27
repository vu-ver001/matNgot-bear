<?php

namespace App\Support;

use App\Models\User;

final class RoleRedirect
{
    public static function routeName(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_STAFF => 'staff.dashboard',
            default => 'customer.dashboard',
        };
    }
}
