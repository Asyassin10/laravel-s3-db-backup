<?php

return [
    'local_backup_path' => storage_path('app/backups'),
    's3_prefix' => 'database-backups',
    's3_disk' => 's3',
    'gzip' => true,
    'databases' => [
        'mysql' => [
            'dump_command' => env('DB_DUMP_COMMAND_PATH', 'mysqldump'),
            'use_single_transaction' => true,
            'timeout' => 300,
        ],
        'pgsql' => [
            'dump_command' => env('PG_DUMP_PATH', 'pg_dump'),
            'timeout' => 300,
        ],
    ],
];