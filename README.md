# Laravel S3 Database Backup

Package for database backups with upload to AWS S3

## Installation

1. **Install the package via Composer:**

```bash
composer require yassine-as/laravel-s3-db-backup
```

2. **Publish the configuration file:**

```bash
php artisan s3-db-backup:install
```

3. **Configure your backup settings:**
   * Open `config/s3-db-backup.php`
   * Set your local backup path, S3 disk, S3 prefix folder, and gzip option.
   * Make sure your AWS S3 credentials and disk are configured in `config/s3-db-backup.php` and `.env`

## Environment Variables (.env)

Make sure to add the following variables to your `.env` file for AWS S3 and dump commands paths if needed:

```env
# AWS S3 Configuration (required)
AWS_ACCESS_KEY_ID=your_aws_access_key_id
AWS_SECRET_ACCESS_KEY=your_aws_secret_access_key
AWS_DEFAULT_REGION=your_aws_region
AWS_BUCKET=your_s3_bucket_name

# Optional: Custom path to mysqldump command if not in PATH
DB_DUMP_COMMAND_PATH=/usr/bin/mysqldump

# Optional: Custom path to pg_dump command if using PostgreSQL
PG_DUMP_PATH=/usr/bin/pg_dump
```

## Usage

Run the backup command:

```bash
php artisan db:backup-to-s3
```

### Options

* `--connection=` Specify the database connection (defaults to your default connection)
* `--local-only` Skip uploading the backup to S3 (backup saved locally only)
* `--clean` Delete local backup file after successful upload to S3

### Example

Backup default database, upload to S3, and delete local file:

```bash
php artisan db:backup-to-s3 --clean
```

Backup MySQL connection only locally (no S3 upload):

```bash
php artisan db:backup-to-s3 --connection=mysql --local-only
```

## Requirements

* PHP 8.0+
* Laravel 9 or 10
* AWS S3 configured in Laravel filesystems and `.env`
* `mysqldump` or `pg_dump` installed and accessible in your server PATH or specified in `.env`

## Notes

* Backups are compressed with gzip by default.
* The package supports MySQL and PostgreSQL.
* Be sure your database user has permission to run dump commands.
* Keep your AWS credentials secure.

## Author

Yassine ait sidi brahim — yassineaitsidibrahim@email.com
