<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use App\Services\GoogleDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function __construct(
        private GoogleDriveService $googleDrive,
        private BackupService $backupService
    ) {}

    public function index(): View
    {
        $backups = Backup::latest()->paginate(15);
        $isConnected = $this->googleDrive->isConnected();

        return view('admin.backups.index', compact('backups', 'isConnected'));
    }

    public function connect(Request $request): RedirectResponse
    {
        if ($request->has('code')) {
            $this->googleDrive->handleCallback($request->input('code'));

            return redirect()->route('admin.backups.index')
                ->with('success', 'Google Drive connected successfully.');
        }

        return redirect()->to($this->googleDrive->getAuthUrl());
    }

    public function disconnect(): RedirectResponse
    {
        $this->googleDrive->disconnect();

        return redirect()->route('admin.backups.index')
            ->with('success', 'Google Drive disconnected.');
    }

    public function store(): RedirectResponse
    {
        if (!$this->googleDrive->isConnected()) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Please connect to Google Drive first.');
        }

        $running = Backup::where('status', Backup::STATUS_RUNNING)->exists();
        if ($running) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'A backup is already in progress.');
        }

        $backup = $this->backupService->createBackup(Backup::TYPE_MANUAL);

        if ($backup->status === Backup::STATUS_COMPLETED) {
            return redirect()->route('admin.backups.index')
                ->with('success', "Backup completed: {$backup->formattedSize()}");
        }

        return redirect()->route('admin.backups.index')
            ->with('error', "Backup failed: {$backup->error}");
    }

    public function destroy(Backup $backup): RedirectResponse
    {
        $this->backupService->deleteBackup($backup);

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup deleted.');
    }

    public function prune(): RedirectResponse
    {
        $deleted = $this->backupService->pruneOldBackups();

        return redirect()->route('admin.backups.index')
            ->with('success', "Pruned {$deleted} old backup(s).");
    }
}
