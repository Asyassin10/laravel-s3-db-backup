<?php

namespace YassineAs\S3DbBackup\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 's3-db-backup:install';
    protected $description = 'Install the S3 Database Backup package';

    public function handle()
    {
        $this->info('Installing S3 Database Backup package...');

        $this->call('vendor:publish', [
            '--provider' => 'YassineAs\S3DbBackup\S3DbBackupServiceProvider',
            '--tag' => 'config'
        ]);

        $this->info('Package installed successfully. Please configure your settings in config/s3-db-backup.php');
    }
}
