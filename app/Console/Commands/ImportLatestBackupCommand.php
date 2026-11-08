<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Spatie\Backup\BackupDestination\Backup;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Process\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\BackupDestination;
use ZipArchive;

#[Description("Import the latest production backup into this environment's database and anonymise customer data")]
#[Signature('coeliac:import-latest-backup')]
class ImportLatestBackupCommand extends Command
{
    public function handle(Factory $process, Filesystem $files): int
    {
        if ($this->laravel->isProduction()) {
            $this->error('This command can not be run in production.');

            return self::FAILURE;
        }

        $backup = BackupDestination::create('backup', config('backup.backup.name'))->newestBackup();

        if ( ! $backup instanceof Backup) {
            $this->error('No backups found.');

            return self::FAILURE;
        }

        $this->info("Downloading {$backup->path()}");

        $directory = storage_path('app/backup-import');
        $zipPath = "{$directory}/" . basename($backup->path());

        $files->ensureDirectoryExists($directory);

        try {
            file_put_contents($zipPath, $backup->stream());

            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                $this->error('Unable to open the backup zip.');

                return self::FAILURE;
            }

            $dumpName = collect(range(0, $zip->numFiles - 1))
                ->map(fn (int $index): string|false => $zip->getNameIndex($index))
                ->first(fn (string|false $name): bool => $name && Str::is('db-dumps/*.sql', $name));

            if ( ! $dumpName) {
                $this->error('No database dump found in the backup.');

                return self::FAILURE;
            }

            $this->info('Importing database, this may take a while');

            /** @var array{host: string, port: string|int, username: string, password: string|null, database: string} $connection */
            $connection = config('database.connections.' . config('database.default'));

            $result = $process
                ->newPendingProcess()
                ->forever()
                ->env(['MYSQL_PWD' => (string) $connection['password']])
                ->input($zip->getStream($dumpName))
                ->run([
                    'mysql',
                    "--host={$connection['host']}",
                    "--port={$connection['port']}",
                    "--user={$connection['username']}",
                    $connection['database'],
                ]);

            $zip->close();

            $this->info('Anonymising data');

            $this->anonymiseData();

            if ($result->failed()) {
                $this->error('The import failed, any data that was imported has been anonymised.');
                $this->error($result->errorOutput());

                return self::FAILURE;
            }

            $this->info('Import complete');

            return self::SUCCESS;
        } finally {
            $files->delete($zipPath);
        }
    }

    protected function anonymiseData(): void
    {
        $this->anonymise('shop_customers', [
            'name' => DB::raw("CONCAT('Customer ', id)"),
            'email' => DB::raw("CONCAT('customer-', id, '@example.com')"),
            'phone' => null,
        ]);

        $this->anonymise('shop_shipping_addresses', [
            'name' => DB::raw("CONCAT('Customer ', id)"),
            'line_1' => DB::raw("CONCAT(id, ' Test Street')"),
            'line_2' => null,
            'line_3' => null,
            'town' => 'Anytown',
            'county' => null,
            'postcode' => 'AB1 2CD',
        ]);

        $this->anonymise('shop_order_reviews', ['name' => DB::raw("CONCAT('Customer ', id)")]);
        $this->anonymise('shop_feedback', ['name' => DB::raw("CONCAT('Customer ', id)")]);

        $this->anonymise('wheretoeat_reviews', ['ip' => '127.0.0.1']);
        $this->anonymise('wheretoeat_reviews', ['name' => DB::raw("CONCAT('Reviewer ', id)")], whereNotNull: 'name');
        $this->anonymise('wheretoeat_reviews', ['email' => DB::raw("CONCAT('reviewer-', id, '@example.com')")], whereNotNull: 'email');

        $this->anonymise('comments', [
            'name' => DB::raw("CONCAT('Commenter ', id)"),
            'email' => DB::raw("CONCAT('commenter-', id, '@example.com')"),
        ]);

        $this->anonymise('competition_entries', [
            'name' => DB::raw("CONCAT('Entrant ', id)"),
            'email' => DB::raw("CONCAT('entrant-', id, '@example.com')"),
        ]);

        $this->anonymise('wheretoeat_place_recommendation', [
            'name' => DB::raw("CONCAT('Recommender ', id)"),
            'email' => DB::raw("CONCAT('recommender-', id, '@example.com')"),
        ]);

        $this->anonymise('wheretoeat_suggested_edits', ['ip' => '127.0.0.1']);

        $this->anonymise('notification_emails', [
            'email_address' => DB::raw("CONCAT('notification-', id, '@example.com')"),
        ]);

        $this->anonymise('shop_payment_responses', ['charge_id' => null, 'response' => '{}']);
        $this->anonymise('shop_payment_refunds', ['refund_id' => null, 'response' => null]);

        $this->anonymise('apple_mobile_pass_devices', ['push_token' => '']);

        if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            DB::table('failed_jobs')->truncate();
        }
    }

    /** @param array<string, mixed> $values */
    protected function anonymise(string $table, array $values, ?string $whereNotNull = null): void
    {
        if ( ! DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        DB::table($table)
            ->when($whereNotNull, fn (Builder $query, string $column) => $query->whereNotNull($column))
            ->update($values);
    }
}
