<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreenContent extends Model
{
    protected $fillable = ['mosque_setting_id', 'type', 'title', 'body', 'media_path', 'starts_at', 'ends_at', 'is_active', 'sort_order'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function mosqueSetting(): BelongsTo
    {
        return $this->belongsTo(MosqueSetting::class);
    }
}
