<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--type=scheduled}';

    protected $description = 'Run a database and storage backup to Google Drive';

    public function handle(BackupService $backupService): int
    {
        $running = Backup::where('status', Backup::STATUS_RUNNING)->exists();

        if ($running) {
            $this->error('A backup is already in progress.');

            return Command::FAILURE;
        }

        $type = $this->option('type') ?? Backup::TYPE_SCHEDULED;

        $this->info("Starting {$type} backup...");

        $backup = $backupService->createBackup($type);

        if ($backup->status === Backup::STATUS_COMPLETED) {
            $this->info("Backup completed: {$backup->formattedSize()}");
            if ($backup->google_drive_link) {
                $this->info("Google Drive: {$backup->google_drive_link}");
            }

            return Command::SUCCESS;
        }

        $this->error("Backup failed: {$backup->error}");

        return Command::FAILURE;
    }
}
