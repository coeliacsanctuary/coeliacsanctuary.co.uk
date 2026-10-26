<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Actions\Shop\FinanceService\SendOrderToFinanceServiceAction;
use App\Events\Shop\OrderPaidEvent;
use App\Listeners\Shop\SendOrderToFinanceService;
use App\Models\Shop\ShopOrder;
use Database\Seeders\ShopScaffoldingSeeder;
use Exception;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendOrderToFinanceServiceTest extends TestCase
{
    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        $this->order = $this->build(ShopOrder::class)->asPaid()->create();
    }

    #[Test]
    public function itCallsTheSendOrderToFinanceServiceAction(): void
    {
        $this->expectAction(SendOrderToFinanceServiceAction::class, [
            fn (ShopOrder $order) => $order->is($this->order),
        ]);

        app(SendOrderToFinanceService::class)->handle(new OrderPaidEvent($this->order));
    }

    #[Test]
    public function itLogsAnErrorIfTheActionThrowsAnException(): void
    {
        $this->expectAction(SendOrderToFinanceServiceAction::class, then: fn ($mock) => $mock->andThrow(new Exception('Finance service is down')));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn (string $message, array $context) => $message === 'Unable to send order to finance service' && $context['message'] === 'Finance service is down');

        app(SendOrderToFinanceService::class)->handle(new OrderPaidEvent($this->order));
    }
}
