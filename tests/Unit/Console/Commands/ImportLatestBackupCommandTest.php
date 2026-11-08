<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ImportLatestBackupCommand;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReview;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopFeedback;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopPaymentRefund;
use App\Models\Shop\ShopPaymentResponse;
use App\Models\Shop\ShopShippingAddress;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use ZipArchive;

class ImportLatestBackupCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('backup');
        Process::fake();
    }

    #[Test]
    public function itRefusesToRunInProduction(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->storeBackup();

        $customer = $this->create(ShopCustomer::class, quietly: true);

        $this->artisan(ImportLatestBackupCommand::class)
            ->expectsOutputToContain('can not be run in production')
            ->assertFailed();

        Process::assertNothingRan();

        $this->assertSame($customer->name, $customer->refresh()->name);
    }

    #[Test]
    public function itFailsWhenThereAreNoBackups(): void
    {
        $this->artisan(ImportLatestBackupCommand::class)
            ->expectsOutputToContain('No backups found')
            ->assertFailed();

        Process::assertNothingRan();
    }

    #[Test]
    public function itImportsTheDumpIntoTheConfiguredDatabase(): void
    {
        $this->storeBackup();

        $this->artisan(ImportLatestBackupCommand::class)->assertSuccessful();

        $database = config('database.connections.' . config('database.default') . '.database');

        Process::assertRan(fn (PendingProcess $process, ProcessResult $result): bool => $process->command[0] === 'mysql'
            && last($process->command) === $database);
    }

    #[Test]
    public function itAnonymisesCustomerData(): void
    {
        $this->storeBackup();

        $customer = $this->create(ShopCustomer::class, quietly: true);
        $address = $this->create(ShopShippingAddress::class, ['customer_id' => $customer->id, 'line_3' => 'Somewhere', 'county' => 'Cheshire'], quietly: true);
        $orderReview = $this->create(ShopOrderReview::class, ['order_id' => 1, 'invitation_id' => 1], quietly: true);
        $feedback = $this->create(ShopFeedback::class, ['product_id' => 1], quietly: true);
        $eateryReview = $this->create(EateryReview::class, ['wheretoeat_id' => 1], quietly: true);
        $comment = $this->create(Comment::class, ['commentable_type' => Blog::class, 'commentable_id' => 1], quietly: true);
        $recommendation = $this->create(EateryRecommendation::class, quietly: true);
        $notificationEmail = $this->create(NotificationEmail::class, ['user_id' => $customer->id, 'data' => []]);
        $paymentResponse = $this->create(ShopPaymentResponse::class, ['payment_id' => 1], quietly: true);
        $paymentRefund = $this->create(ShopPaymentRefund::class, ['payment_id' => 1, 'order_id' => 1], quietly: true);

        $competitionEntryId = Str::uuid()->toString();

        DB::table('competition_entries')->insert(['id' => $competitionEntryId, 'competition_id' => 1, 'name' => 'Real Name', 'email' => 'real@email.com', 'entry_type' => 'form']);
        DB::table('wheretoeat_suggested_edits')->insert(['id' => 1, 'wheretoeat_id' => 1, 'field' => 'phone', 'value' => '123', 'ip' => '1.2.3.4']);
        DB::table('apple_mobile_pass_devices')->insert(['id' => 'device', 'push_token' => 'real-token']);
        DB::table('failed_jobs')->insert(['uuid' => Str::uuid()->toString(), 'connection' => 'redis', 'queue' => 'default', 'payload' => '{}', 'exception' => 'boom']);

        $this->artisan(ImportLatestBackupCommand::class)->assertSuccessful();

        $customer->refresh();
        $this->assertSame("Customer {$customer->id}", $customer->name);
        $this->assertSame("customer-{$customer->id}@example.com", $customer->email);
        $this->assertNull($customer->phone);

        $address->refresh();
        $this->assertSame("Customer {$address->id}", $address->name);
        $this->assertSame("{$address->id} Test Street", $address->line_1);
        $this->assertNull($address->line_2);
        $this->assertNull($address->line_3);
        $this->assertSame('Anytown', $address->town);
        $this->assertNull($address->county);
        $this->assertSame('AB1 2CD', $address->postcode);

        $this->assertSame("Customer {$orderReview->id}", $orderReview->refresh()->name);
        $this->assertSame("Customer {$feedback->id}", $feedback->refresh()->name);

        $eateryReview->refresh();
        $this->assertSame("Reviewer {$eateryReview->id}", $eateryReview->name);
        $this->assertSame("reviewer-{$eateryReview->id}@example.com", $eateryReview->email);
        $this->assertSame('127.0.0.1', $eateryReview->ip);

        $comment->refresh();
        $this->assertSame("Commenter {$comment->id}", $comment->name);
        $this->assertSame("commenter-{$comment->id}@example.com", $comment->email);

        $recommendation->refresh();
        $this->assertSame("Recommender {$recommendation->id}", $recommendation->name);
        $this->assertSame("recommender-{$recommendation->id}@example.com", $recommendation->email);

        $this->assertSame("notification-{$notificationEmail->id}@example.com", $notificationEmail->refresh()->email_address);

        $paymentResponse->refresh();
        $this->assertNull($paymentResponse->charge_id);
        $this->assertSame([], $paymentResponse->response);

        $paymentRefund->refresh();
        $this->assertNull($paymentRefund->refund_id);
        $this->assertNull($paymentRefund->response);

        $competitionEntry = DB::table('competition_entries')->first();
        $this->assertSame("Entrant {$competitionEntryId}", $competitionEntry->name);
        $this->assertSame("entrant-{$competitionEntryId}@example.com", $competitionEntry->email);

        $this->assertSame('127.0.0.1', DB::table('wheretoeat_suggested_edits')->value('ip'));
        $this->assertSame('', DB::table('apple_mobile_pass_devices')->value('push_token'));
        $this->assertDatabaseEmpty('failed_jobs');
    }

    #[Test]
    public function itStillAnonymisesWhenTheImportFails(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'Import exploded', exitCode: 1)]);

        $this->storeBackup();

        $customer = $this->create(ShopCustomer::class, quietly: true);

        $this->artisan(ImportLatestBackupCommand::class)
            ->expectsOutputToContain('Import exploded')
            ->assertFailed();

        $this->assertSame("Customer {$customer->id}", $customer->refresh()->name);
    }

    #[Test]
    public function itLeavesNullReviewerDetailsNull(): void
    {
        $this->storeBackup();

        $eateryReview = $this->create(EateryReview::class, ['wheretoeat_id' => 1, 'name' => null, 'email' => null], quietly: true);

        $this->artisan(ImportLatestBackupCommand::class)->assertSuccessful();

        $eateryReview->refresh();
        $this->assertNull($eateryReview->name);
        $this->assertNull($eateryReview->email);
    }

    protected function storeBackup(): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'backup');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);
        $zip->addFromString('db-dumps/mysql-test.sql', 'SELECT 1;');
        $zip->close();

        Storage::disk('backup')->put(config('backup.backup.name') . '/2026-11-08-10-00-00.zip', file_get_contents($zipPath));

        unlink($zipPath);
    }
}
