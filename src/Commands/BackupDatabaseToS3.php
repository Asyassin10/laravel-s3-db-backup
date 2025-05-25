<?php

namespace YassineAs\S3DbBackup\Commands;

use Illuminate\Console\Command;
use YassineAs\S3DbBackup\Services\S3BackupService;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup-to-s3
                            {--connection= : Database connection name}
                            {--local-only : Skip S3 upload}
                            {--clean : Delete local backup after upload}';

    protected $description = 'Backup database to S3 with smart sync';

    public function handle(S3BackupService $backupService)
    {
        $connection = $this->option('connection') ?: config('database.default');

        try {
            // Step 1: Create local backup
            $localPath = $backupService->createBackup($connection);
            $this->info("✅ Local backup created: " . basename($localPath));

            // Step 2: Sync to S3 (unless --local-only)
            if (!$this->option('local-only')) {
                $s3Path = $backupService->syncToS3($localPath);
                $this->info("☁️ S3 sync result: {$s3Path}");
            }

            // Step 3: Clean up if requested
            if ($this->option('clean') && !$this->option('local-only')) {
                unlink($localPath);
                $this->info("♻️ Local backup cleaned");
            }

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}