<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportCase extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'WAITING';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_CLOSED = 'CLOSED';

    public const STATUS_LABELS = [
        self::STATUS_WAITING => 'Chưa xử lý',
        self::STATUS_IN_PROGRESS => 'Đang xử lý',
        self::STATUS_CLOSED => 'Đã kết thúc',
    ];

    protected $fillable = [
        'case_code',
        'conversation_id',
        'customer_id',
        'assigned_staff_id',
        'order_id',
        'status',
        'priority',
        'channel',
        'opened_at',
        'closed_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'support_case_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'support_case_id')->latestOfMany('id');
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getAssignedHandlerIdAttribute(): ?int
    {
        return $this->assigned_staff_id;
    }

    public function setAssignedHandlerIdAttribute(?int $value): void
    {
        $this->attributes['assigned_staff_id'] = $value;
    }

    public function assignedHandler(): BelongsTo
    {
        return $this->assignedStaff();
    }

    public function isHandledBy(User $user): bool
    {
        return $this->isInProgress() && (int) $this->assigned_staff_id === (int) $user->id;
    }

    public function canChat(User $user): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->isWaiting()) {
            return $user->role === User::ROLE_ADMIN;
        }

        return $this->isHandledBy($user);
    }

    public function canBeHandledBy(User $user): bool
    {
        return $this->isHandledBy($user);
    }
}
