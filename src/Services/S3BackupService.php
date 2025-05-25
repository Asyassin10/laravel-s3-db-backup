<?php

namespace YassineAs\S3DbBackup\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class S3BackupService
{
    protected $localPath;
    protected $s3Path;
    protected $s3Disk;

    public function __construct()
    {
        $this->localPath = config('s3-db-backup.local_backup_path');
        $this->s3Path = config('s3-db-backup.s3_prefix');
        $this->s3Disk = config('s3-db-backup.s3_disk');
    }

    public function createBackup(string $connection): string
    {
        $driver = config("database.connections.{$connection}.driver");
        $filename = "backup-{$connection}-" . now()->format('Y-m-d-H-i-s') . '.sql';
        $fullPath = "{$this->localPath}/{$filename}";

        $this->ensureDirectoryExists($this->localPath);
        $this->runDumpCommand($driver, $connection, $fullPath);

        if (config('s3-db-backup.gzip')) {
            $this->compressFile($fullPath);
            $fullPath .= '.gz';
        }

        return $fullPath;
    }

    public function syncToS3(string $localPath): string
    {
        $filename = basename($localPath);
        $s3FullPath = "{$this->s3Path}/{$filename}";

        if (Storage::disk($this->s3Disk)->exists($s3FullPath)) {
            $localModified = filemtime($localPath);
            $s3Modified = Storage::disk($this->s3Disk)->lastModified($s3FullPath);

            if ($localModified <= $s3Modified) {
                return "File unchanged, skipping upload";
            }
        }

        Storage::disk($this->s3Disk)->put($s3FullPath, fopen($localPath, 'r'));
        return $s3FullPath;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    protected function runDumpCommand(string $driver, string $connection, string $outputPath): void
    {
        $dbConfig = config("database.connections.{$connection}");
        $dumpConfig = config("s3-db-backup.databases.{$driver}");

        $command = $this->buildDumpCommand($driver, $dbConfig, $dumpConfig, $outputPath);

        $process = new Process(explode(' ', $command));
        $process->setTimeout($dumpConfig['timeout']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    protected function buildDumpCommand(string $driver, array $dbConfig, array $dumpConfig, string $outputPath): string
    {
        if ($driver === 'mysql') {
            return sprintf(
                '%s --user=%s --password=%s --host=%s --port=%s %s %s > %s',
                $dumpConfig['dump_command'],
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                ($dumpConfig['use_single_transaction'] ?? false) ? '--single-transaction' : '',
                escapeshellarg($dbConfig['database']),
                escapeshellarg($outputPath)
            );
        }

        if ($driver === 'pgsql') {
            return sprintf(
                'PGPASSWORD=%s %s -h %s -p %s -U %s -F c -f %s %s',
                escapeshellarg($dbConfig['password']),
                $dumpConfig['dump_command'],
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($outputPath),
                escapeshellarg($dbConfig['database'])
            );
        }

        throw new \RuntimeException("Unsupported database driver: {$driver}");
    }

    protected function compressFile(string $filePath): void
    {
        $process = new Process(['gzip', $filePath]);
        $process->mustRun();
    }
}
