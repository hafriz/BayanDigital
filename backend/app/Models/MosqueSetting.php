<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class MosqueSetting extends Model
{
    protected $fillable = [
        'public_id',
        'type',
        'name',
        'zone_code',
        'status',
        'contact_name',
        'contact_phone',
        'contact_email',
        'address',
        'prayer_offsets',
        'iqamah_minutes',
        'silent_mode_minutes',
        'screen_theme',
        'time_format',
        'logo_url',
        'google_calendar_ics_url',
    ];

    protected $casts = [
        'prayer_offsets' => 'array',
        'iqamah_minutes' => 'array',
        'silent_mode_minutes' => 'integer',
    ];

    public function screenContents(): HasMany
    {
        return $this->hasMany(ScreenContent::class);
    }

    public function screenDevices(): HasMany
    {
        return $this->hasMany(ScreenDevice::class);
    }
}
