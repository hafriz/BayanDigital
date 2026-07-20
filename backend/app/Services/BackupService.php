<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupService
{
    public function __construct(
        private GoogleDriveService $googleDrive
    ) {}

    public function createBackup(string $type = Backup::TYPE_MANUAL): Backup
    {
        $backup = Backup::create([
            'type' => $type,
            'status' => Backup::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            $timestamp = now()->format('Y-m-d_H-i-s');

            $dbFile = $this->dumpDatabase($timestamp);
            $backup->database_file = $dbFile['name'];
            $backup->database_size = $dbFile['size'];

            $storageFile = $this->zipStorage($timestamp);
            $backup->storage_file = $storageFile['name'];
            $backup->storage_size = $storageFile['size'];

            $backup->total_size = $backup->database_size + $backup->storage_size;

            $this->uploadToGoogleDrive($backup, $dbFile['path'], $storageFile['path']);

            $backup->status = Backup::STATUS_COMPLETED;
            $backup->completed_at = now();
            $backup->save();

            @unlink($dbFile['path']);
            @unlink($storageFile['path']);
        } catch (\Exception $e) {
            $backup->status = Backup::STATUS_FAILED;
            $backup->error = $e->getMessage();
            $backup->completed_at = now();
            $backup->save();
        }

        return $backup->fresh();
    }

    private function dumpDatabase(string $timestamp): array
    {
        $name = "database_{$timestamp}.sql";
        $path = storage_path("app/{$name}");

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Database dump failed: ' . implode("\n", $output));
        }

        return [
            'name' => $name,
            'path' => $path,
            'size' => File::size($path),
        ];
    }

    private function zipStorage(string $timestamp): array
    {
        $name = "storage_{$timestamp}.zip";
        $path = storage_path("app/{$name}");

        $storagePath = storage_path();
        $tmpZip = $path . '.tmp';
        $zip = new \ZipArchive();

        if ($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create storage zip archive');
        }

        $this->addDirectoryToZip($zip, $storagePath, $storagePath);
        $zip->close();

        rename($tmpZip, $path);

        return [
            'name' => $name,
            'path' => $path,
            'size' => File::size($path),
        ];
    }

    private function addDirectoryToZip(\ZipArchive $zip, string $directory, string $baseDir): void
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $relativePath = ltrim(str_replace($baseDir, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getPathname(), $relativePath);
        }
    }

    private function uploadToGoogleDrive(Backup $backup, string $dbPath, string $storagePath): void
    {
        if (!$this->googleDrive->isConnected()) {
            return;
        }

        $folderId = $this->googleDrive->getOrCreateFolder(config('backup.google.folder_name'));

        $dbResult = $this->googleDrive->uploadFile($dbPath, basename($dbPath), $folderId);
        $storageResult = $this->googleDrive->uploadFile($storagePath, basename($storagePath), $folderId);

        $backup->google_drive_id = $dbResult['id'];
        $backup->google_drive_link = $dbResult['link'];
        $backup->database_size = $dbResult['size'] ?? $backup->database_size;
        $backup->storage_size = $storageResult['size'] ?? $backup->storage_size;
        $backup->total_size = $backup->database_size + $backup->storage_size;
        $backup->save();
    }

    public function deleteBackup(Backup $backup): bool
    {
        if ($backup->google_drive_id && $this->googleDrive->isConnected()) {
            $this->googleDrive->deleteFile($backup->google_drive_id);
        }

        if ($backup->database_file) {
            @unlink(storage_path("app/{$backup->database_file}"));
        }
        if ($backup->storage_file) {
            @unlink(storage_path("app/{$backup->storage_file}"));
        }

        return $backup->delete();
    }

    public function pruneOldBackups(): int
    {
        $keepDays = config('backup.schedule.keep_days', 30);
        $cutoff = now()->subDays($keepDays);

        $oldBackups = Backup::where('completed_at', '<', $cutoff)->get();
        $deleted = 0;

        foreach ($oldBackups as $backup) {
            if ($this->deleteBackup($backup)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
