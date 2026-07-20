<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScreenDevice extends Model
{
    protected $fillable = [
        'request_id',
        'pairing_code',
        'device_name',
        'status',
        'expires_at',
        'approved_at',
        'last_seen_at',
        'approved_by',
    ];

    protected $hidden = ['device_token', 'token_hash'];

    protected function casts(): array
    {
        return [
            'device_token' => 'encrypted',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function mosqueSetting(): BelongsTo
    {
        return $this->belongsTo(MosqueSetting::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approve(User $user): void
    {
        $token = Str::random(64);

        $this->forceFill([
            'status' => 'approved',
            'device_token' => $token,
            'token_hash' => hash('sha256', $token),
            'approved_at' => now(),
            'approved_by' => $user->id,
        ])->save();
    }

    public function isUsable(): bool
    {
        return $this->status === 'approved' && $this->token_hash !== null;
    }
}
