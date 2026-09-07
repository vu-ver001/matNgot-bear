<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, MustVerifyEmail, Notifiable, SoftDeletes;

    public const ROLE_CUSTOMER = 'CUSTOMER';

    public const ROLE_STAFF = 'STAFF';

    public const ROLE_ADMIN = 'ADMIN';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_BLOCKED = 'BLOCKED';

    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'google_id',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
        'address',
        'avatar',
        'province',
        'district',
        'ward',
        'address_detail',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function recordLogin(): void
    {
        $this->forceFill(['last_login_at' => now()])->saveQuietly();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : null;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    public function staffConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'staff_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
