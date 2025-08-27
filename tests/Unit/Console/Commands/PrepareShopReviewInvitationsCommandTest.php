<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Models\Shop\ShopPaymentRefund;
use PHPUnit\Framework\Attributes\Test;
use App\Console\Commands\PrepareShopReviewInvitationsCommand;
use App\Jobs\Shop\SendReviewInvitationJob;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PrepareShopReviewInvitationsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        Carbon::setTestNow('2024-04-15');

        Bus::fake();
    }

    #[Test]
    public function itDoesntDispatchTheJobForOrdersThatArentMarkedAsShipped(): void
    {
        $this->build(ShopOrder::class)->asPaid()->create();

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDoesntDispatchTheJobsForOrdersThatHaveNotBeenShippedToday(): void
    {
        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::yesterday())->create();

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDoesntDispatchTheJobForOrdersThatAlreadyHaveAnInvitation(): void
    {
        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asShipped()->create();
        $order->reviewInvitation()->create();

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDoesntDispatchTheJobForOrdersThatHaveRefunds(): void
    {
        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asShipped()->create();

        $this->create(ShopPaymentRefund::class, [
            'order_id' => $order->id,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDispatchesTheJobForQualifiedOrders(): void
    {
        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subDays(10))->create([
            'postage_country_id' => 1,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertDispatched(SendReviewInvitationJob::class);
    }

    #[Test]
    public function itDispatchesUKOrdersAfterTheRelevantPeriod(): void
    {
        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subDays(9))->create([
            'postage_country_id' => 1,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();

        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subDays(10))->create([
            'postage_country_id' => 1,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertDispatched(SendReviewInvitationJob::class);
    }

    #[Test]
    public function itDispatchesEUOrdersAfterTheRelevantPeriod(): void
    {
        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subDays(9))->create([
            'postage_country_id' => 2,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();

        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subWeeks(2))->create([
            'postage_country_id' => 2,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertDispatched(SendReviewInvitationJob::class);
    }

    #[Test]
    public function itDispatchesUSAndOZOrdersAfterTheRelevantPeriod(): void
    {
        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subDays(9))->create([
            'postage_country_id' => 3,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertNothingDispatched();

        $this->build(ShopOrder::class)->asShipped(shippedAt: Carbon::now()->subWeeks(3))->create([
            'postage_country_id' => 3,
        ]);

        $this->artisan(PrepareShopReviewInvitationsCommand::class);

        Bus::assertDispatched(SendReviewInvitationJob::class);
    }
}
