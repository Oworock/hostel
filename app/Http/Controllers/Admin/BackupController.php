<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemBackupService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function download(string $file)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $safeFile = basename($file);
        abort_unless(Str::endsWith($safeFile, '.zip'), 404);

        $path = storage_path('app/backups/' . $safeFile);
        abort_unless(File::exists($path), 404);

        return response()->download($path, $safeFile);
    }

    public function destroy(string $file, SystemBackupService $backupService)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $deleted = $backupService->deleteBackup($file);
        abort_unless($deleted, 404);

        return back()->with('status', 'Backup deleted successfully.');
    }

    public function restoreDatabase(string $file, SystemBackupService $backupService)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        try {
            $backupService->restoreDatabaseOnly($file);
        } catch (\Throwable $exception) {
            return back()->withErrors(['restore' => 'Database-only restore failed: ' . $exception->getMessage()]);
        }

        return back()->with('status', 'Database restored successfully from backup.');
    }

    public function restoreFiles(string $file, SystemBackupService $backupService)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        try {
            $backupService->restoreFilesOnly($file);
        } catch (\Throwable $exception) {
            return back()->withErrors(['restore' => 'Files-only restore failed: ' . $exception->getMessage()]);
        }

        return back()->with('status', 'Project files restored successfully from backup.');
    }
}
