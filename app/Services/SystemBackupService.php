<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use ZipArchive;

class SystemBackupService
{
    public function createBackup(): string
    {
        $timestamp = now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');

        File::ensureDirectoryExists($backupDir);

        $zipName = "system_backup_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $zipName;
        $tempSqlPath = $backupDir . DIRECTORY_SEPARATOR . "database_backup_{$timestamp}.sql";

        File::put($tempSqlPath, $this->buildSqlDump());

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create backup zip file.');
        }

        $zip->addFile($tempSqlPath, 'database/database.sql');

        $pathsToPack = [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'storage/app/public',
            'artisan',
            'composer.json',
            'composer.lock',
        ];

        if (File::exists(base_path('.env'))) {
            $pathsToPack[] = '.env';
        }

        foreach ($pathsToPack as $path) {
            $absolute = base_path($path);
            if (!File::exists($absolute)) {
                continue;
            }

            if (File::isFile($absolute)) {
                $zip->addFile($absolute, 'system/' . $path);
                continue;
            }

            $this->addDirectoryToZip($zip, $absolute, 'system/' . $path);
        }

        $zip->close();
        File::delete($tempSqlPath);

        $this->pruneOldBackups($backupDir, 2);

        return 'backups/' . $zipName;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $sourceDir, string $zipRoot): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $fullPath = $item->getPathname();
            $relativePath = ltrim(str_replace($sourceDir, '', $fullPath), DIRECTORY_SEPARATOR);
            $entryPath = trim($zipRoot . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath), '/');

            if ($item->isDir()) {
                $zip->addEmptyDir($entryPath);
                continue;
            }

            $zip->addFile($fullPath, $entryPath);
        }
    }

    private function buildSqlDump(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = $connection->getDatabaseName();

        $dump = [
            "-- Hostel System SQL Backup",
            '-- Generated at: ' . now()->toDateTimeString(),
            '-- Database: ' . $database,
            '',
        ];

        $tables = match ($driver) {
            'sqlite' => collect($connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                ->pluck('name')
                ->all(),
            default => collect($connection->select('SHOW TABLES'))
                ->map(fn ($row) => (string) array_values((array) $row)[0])
                ->all(),
        };

        foreach ($tables as $table) {
            $create = match ($driver) {
                'sqlite' => $connection->selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]),
                default => $connection->selectOne("SHOW CREATE TABLE `{$table}`"),
            };

            $createSql = $driver === 'sqlite'
                ? ((array) $create)['sql'] ?? null
                : ((array) $create)['Create Table'] ?? null;

            if ($createSql) {
                $dump[] = "DROP TABLE IF EXISTS `{$table}`;";
                $dump[] = rtrim($createSql, ';') . ';';
            }

            $rows = $connection->table($table)->get();
            foreach ($rows as $row) {
                $values = array_map(
                    fn ($value) => $this->toSqlValue($value),
                    array_values((array) $row)
                );

                $dump[] = "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ');';
            }

            $dump[] = '';
        }

        return implode(PHP_EOL, $dump);
    }

    private function toSqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'" . str_replace("'", "''", (string) $value) . "'";
    }

    private function pruneOldBackups(string $backupDir, int $keep): void
    {
        $files = collect(File::files($backupDir))
            ->filter(fn (\SplFileInfo $file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn (\SplFileInfo $file) => $file->getMTime())
            ->values();

        foreach ($files->slice($keep) as $oldFile) {
            File::delete($oldFile->getPathname());
        }
    }

    public function recentBackups(int $limit = 10): Collection
    {
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        return collect(File::files($backupDir))
            ->filter(fn (\SplFileInfo $file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn (\SplFileInfo $file) => $file->getMTime())
            ->take($limit)
            ->values()
            ->map(function (\SplFileInfo $file): array {
                $bytes = (int) $file->getSize();

                return [
                    'file' => $file->getFilename(),
                    'size_bytes' => $bytes,
                    'size_human' => $this->humanSize($bytes),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            });
    }

    public function deleteBackup(string $file): bool
    {
        $safeFile = basename($file);
        if ($safeFile === '' || !str_ends_with($safeFile, '.zip')) {
            return false;
        }

        $path = storage_path('app/backups/' . $safeFile);
        if (!File::exists($path)) {
            return false;
        }

        return File::delete($path);
    }

    public function restoreBackup(string $file): void
    {
        $this->restoreBackupParts($file, true, true);
    }

    public function restoreDatabaseOnly(string $file): void
    {
        $this->restoreBackupParts($file, true, false);
    }

    public function restoreFilesOnly(string $file): void
    {
        $this->restoreBackupParts($file, false, true);
    }

    private function restoreBackupParts(string $file, bool $restoreDatabase, bool $restoreFiles): void
    {
        $safeFile = basename($file);
        if ($safeFile === '' || !str_ends_with($safeFile, '.zip')) {
            throw new \RuntimeException('Invalid backup file.');
        }

        $zipPath = storage_path('app/backups/' . $safeFile);
        if (!File::exists($zipPath)) {
            throw new \RuntimeException('Backup file not found.');
        }

        $tmpDir = storage_path('app/backups/restore_tmp_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)));
        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open backup archive.');
        }

        if (!$zip->extractTo($tmpDir)) {
            $zip->close();
            throw new \RuntimeException('Unable to extract backup archive.');
        }
        $zip->close();

        try {
            if ($restoreDatabase) {
                $dbDumpPath = $tmpDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sql';
                if (File::exists($dbDumpPath)) {
                    $this->restoreDatabaseFromDump($dbDumpPath);
                }
            }

            if ($restoreFiles) {
                $systemRoot = $tmpDir . DIRECTORY_SEPARATOR . 'system';
                if (File::isDirectory($systemRoot)) {
                    $this->syncDirectory($systemRoot, base_path());
                }
            }
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    private function restoreDatabaseFromDump(string $dbDumpPath): void
    {
        $sql = File::get($dbDumpPath);
        $statements = preg_split("/;\s*\n/", (string) $sql) ?: [];

        DB::beginTransaction();
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '' || str_starts_with($statement, '--')) {
                    continue;
                }

                DB::unprepared($statement . ';');
            }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function syncDirectory(string $source, string $destination): void
    {
        $items = File::allFiles($source);

        foreach ($items as $item) {
            $sourcePath = $item->getPathname();
            $relativePath = ltrim(str_replace($source, '', $sourcePath), DIRECTORY_SEPARATOR);
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($sourcePath, $targetPath);
        }
    }
}
