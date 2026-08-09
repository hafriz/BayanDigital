<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MosqueSetting extends Model
{
    protected $fillable = [
        'public_id',
        'public_slug',
        'type',
        'name',
        'zone_code',
        'status',
        'contact_name',
        'contact_phone',
        'contact_email',
        'address',
        'committee',
        'prayer_offsets',
        'iqamah_minutes',
        'prayer_alerts_enabled',
        'pre_prayer_beep_minutes',
        'silent_mode_minutes',
        'screen_theme',
        'time_format',
        'logo_url',
        'donation_qr_url',
        'donation_qr_image',
        'donation_caption',
        'donation_account',
        'google_calendar_ics_url',
        'screen_sleep_enabled',
        'screen_sleep_mode',
        'screen_sleep_time',
        'sleep_after_isyak_minutes',
        'screen_wake_mode',
        'screen_wake_time',
        'wake_before_subuh_minutes',
    ];

    protected $casts = [
        'prayer_offsets' => 'array',
        'iqamah_minutes' => 'array',
        'prayer_alerts_enabled' => 'boolean',
        'pre_prayer_beep_minutes' => 'integer',
        'committee' => 'array',
        'silent_mode_minutes' => 'integer',
        'screen_sleep_enabled' => 'boolean',
        'sleep_after_isyak_minutes' => 'integer',
        'wake_before_subuh_minutes' => 'integer',
    ];

    public function screenContents(): HasMany
    {
        return $this->hasMany(ScreenContent::class);
    }

    public function screenDevices(): HasMany
    {
        return $this->hasMany(ScreenDevice::class);
    }

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(MosqueCommitteeMember::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
