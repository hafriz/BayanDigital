<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'type',
        'status',
        'database_file',
        'storage_file',
        'google_drive_id',
        'google_drive_link',
        'database_size',
        'storage_size',
        'total_size',
        'error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'database_size' => 'integer',
        'storage_size' => 'integer',
        'total_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const TYPE_MANUAL = 'manual';
    public const TYPE_SCHEDULED = 'scheduled';

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function formattedSize(): string
    {
        $bytes = $this->total_size;
        if ($bytes === 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
