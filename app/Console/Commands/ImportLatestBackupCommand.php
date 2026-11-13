<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Spatie\Backup\BackupDestination\Backup;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Process\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Backup\BackupDestination\BackupDestination;
use ZipArchive;

#[Description("Import the latest production backup into this environment's database and anonymise customer data")]
#[Signature('coeliac:import-latest-backup')]
class ImportLatestBackupCommand extends Command
{
    public function handle(Factory $process): int
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

        $this->info("Streaming {$backup->path()}");

        $stream = $backup->stream();
        $dump = $this->readDumpHeader($stream);

        if ( ! Str::is('db-dumps/*.sql', $dump['name'])) {
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
            ->input($this->readDump($stream, $dump['size']))
            ->run([
                'mysql',
                "--host={$connection['host']}",
                "--port={$connection['port']}",
                "--user={$connection['username']}",
                $connection['database'],
            ]);

        fclose($stream);

        $this->info('Anonymising data');

        $this->anonymiseData();

        if ($result->failed()) {
            $this->error('The import failed, any data that was imported has been anonymised.');
            $this->error($result->errorOutput());

            return self::FAILURE;
        }

        $this->info('Import complete');

        return self::SUCCESS;
    }

    /**
     * @param resource $stream
     * @return array{name: string, size: int}
     */
    protected function readDumpHeader($stream): array
    {
        /** @var array{signature: string, method: int, size: int, nameLength: int, extraLength: int}|false $header */
        $header = @unpack('a4signature/x4/vmethod/x8/Vsize/x4/vnameLength/vextraLength', (string) stream_get_contents($stream, 30));

        throw_if(
            ! $header || $header['signature'] !== "PK\x03\x04" || $header['method'] !== ZipArchive::CM_STORE,
            new RuntimeException('The backup is not an uncompressed zip.'),
        );

        $name = (string) stream_get_contents($stream, $header['nameLength']);
        stream_get_contents($stream, $header['extraLength']);

        return ['name' => $name, 'size' => $header['size']];
    }

    /**
     * @param resource $stream
     * @return Generator<int, string>
     */
    protected function readDump($stream, int $size): Generator
    {
        while ($size > 0 && ! feof($stream)) {
            $chunk = (string) fread($stream, min(1024 * 1024, $size));
            $size -= mb_strlen($chunk);

            yield $chunk;
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
