<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MosqueCommitteeMember extends Model
{
    protected $fillable = [
        'name',
        'position',
        'photo_path',
        'phone',
        'email',
        'show_phone_publicly',
        'show_email_publicly',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'show_phone_publicly' => 'boolean',
            'show_email_publicly' => 'boolean',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function mosqueSetting(): BelongsTo
    {
        return $this->belongsTo(MosqueSetting::class);
    }
}
