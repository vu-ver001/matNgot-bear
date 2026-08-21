<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_pinned',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    protected $appends = [
        'slug',
        'status',
    ];

    public function getSlugAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->name);
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'ACTIVE' : 'INACTIVE';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function vouchers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'voucher_categories');
    }
}
