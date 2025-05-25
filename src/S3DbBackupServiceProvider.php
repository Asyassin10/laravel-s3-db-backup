<?php

namespace YassineAs\S3DbBackup;

use Illuminate\Support\ServiceProvider;

class S3DbBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/s3-db-backup.php' => config_path('s3-db-backup.php'),
            ], 'config');

            $this->commands([
                Commands\BackupDatabaseCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/s3-db-backup.php',
            's3-db-backup'
        );

        $this->app->singleton(Services\S3BackupService::class, function () {
            return new Services\S3BackupService();
        });
    }
}
