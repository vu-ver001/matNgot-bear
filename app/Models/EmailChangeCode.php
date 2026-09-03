<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailChangeCode extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'code_hash',
        'expires_at',
        'verified_at',
        'last_sent_at',
        'attempts',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
