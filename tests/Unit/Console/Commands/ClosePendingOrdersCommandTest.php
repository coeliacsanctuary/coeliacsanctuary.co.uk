<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\CloseBasketAction;
use App\Console\Commands\ClosePendingOrdersCommand;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Stripe\PaymentIntent;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class ClosePendingOrdersCommandTest extends TestCase
{
    use MocksStripe;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-04-01 12:00:00');
    }

    #[Test]
    public function itClosesAPendingOrderWhereThePaymentIntentHasntSucceeded(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create([
            'updated_at' => '2024-04-01 10:00:00',
        ]);

        $this->mockRetrievePaymentIntent($order->payment_intent_secret, PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD);

        $this->expectAction(CloseBasketAction::class);

        $this->artisan(ClosePendingOrdersCommand::class);
    }

    #[Test]
    public function itDoesntCloseAPendingOrderWhereThePaymentIntentHasSucceeded(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create([
            'updated_at' => '2024-04-01 10:00:00',
        ]);

        $this->mockRetrievePaymentIntent($order->payment_intent_secret, PaymentIntent::STATUS_SUCCEEDED);

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(ClosePendingOrdersCommand::class);
    }

    #[Test]
    public function itDoesntCloseAPendingOrderThatIsInsideTheTimeout(): void
    {
        $this->build(ShopOrder::class)->asPending()->create([
            'created_at' => '2024-04-01 11:30:00',
            'updated_at' => '2024-04-01 11:45:00',
        ]);

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(ClosePendingOrdersCommand::class);
    }

    #[Test]
    public function itDoesntCloseOrdersThatArentPending(): void
    {
        $this->build(ShopOrder::class)->asBasket()->create([
            'updated_at' => '2024-04-01 10:00:00',
        ]);

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(ClosePendingOrdersCommand::class);
    }

    #[Test]
    public function itDoesntCloseAPendingOrderIfTheIntentCantBeChecked(): void
    {
        $this->build(ShopOrder::class)->asPending()->create([
            'updated_at' => '2024-04-01 10:00:00',
        ]);

        $this->mockPaymentIntentRetrievalFailure();

        $this->mock(CloseBasketAction::class)->shouldNotReceive('handle');

        $this->artisan(ClosePendingOrdersCommand::class);
    }
}
