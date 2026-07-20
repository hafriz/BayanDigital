<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    protected $fillable = ['zone_code', 'prayer_date', 'hijri_date', 'times', 'fetched_at'];

    protected $casts = [
        'prayer_date' => 'date:Y-m-d',
        'times' => 'array',
        'fetched_at' => 'datetime',
    ];
}
