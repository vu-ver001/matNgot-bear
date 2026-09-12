<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'support_case_id',
        'sender_id',
        'content',
        'image_url',
        'images',
        'is_read',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'images' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Lấy danh sách URL tất cả hình ảnh đính kèm (dạng mảng).
     *
     * @return array<string>
     */
    public function getImageUrlsAttribute(): array
    {
        if (! empty($this->images) && is_array($this->images)) {
            return $this->images;
        }
        if (! empty($this->image_url)) {
            $decoded = json_decode($this->image_url, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return [$this->image_url];
        }
        return [];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function supportCase(): BelongsTo
    {
        return $this->belongsTo(SupportCase::class, 'support_case_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
