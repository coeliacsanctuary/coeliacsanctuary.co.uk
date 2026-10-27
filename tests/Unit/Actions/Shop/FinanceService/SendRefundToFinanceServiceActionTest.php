<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\FinanceService;

use App\Actions\Shop\FinanceService\SendRefundToFinanceServiceAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendRefundToFinanceServiceActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopPaymentRefund $refund;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.finance.url' => 'https://finance.test',
            'services.finance.key' => 'secret-key',
        ]);

        $this->order = $this->build(ShopOrder::class)->asPaid()->create();

        $this->refund = $this->build(ShopPaymentRefund::class)
            ->forPayment($this->order->payment)
            ->create(['order_id' => $this->order->id]);

        Http::preventStrayRequests();
        Http::fake(["https://finance.test/api/v1/orders/{$this->order->order_key}/refunds" => Http::response()]);
    }

    #[Test]
    public function itPostsTheRefundToTheFinanceServiceWithTheApiKey(): void
    {
        $this->callAction(SendRefundToFinanceServiceAction::class, $this->refund);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === "https://finance.test/api/v1/orders/{$this->order->order_key}/refunds"
            && $request->hasHeader('Authorization', 'Bearer secret-key'));
    }

    #[Test]
    public function itSendsTheRefundDetails(): void
    {
        $this->refund->update(['refund_id' => 're_123', 'amount' => 1250, 'note' => 'Damaged in post']);

        $this->callAction(SendRefundToFinanceServiceAction::class, $this->refund);

        Http::assertSent(function (Request $request) {
            $this->assertSame((string) $this->refund->id, $request['refund_id']);
            $this->assertEquals($this->refund->created_at->toIso8601String(), $request['refunded_at']);
            $this->assertEquals('re_123', $request['stripe_refund_id']);
            $this->assertEquals(1250, $request['amount']);
            $this->assertEquals('Damaged in post', $request['reason']);

            return true;
        });
    }

    #[Test]
    public function itTruncatesTheReasonToTheFinanceServiceLimit(): void
    {
        $this->refund->update(['note' => str_repeat('a', 300)]);

        $this->callAction(SendRefundToFinanceServiceAction::class, $this->refund);

        Http::assertSent(function (Request $request) {
            $this->assertLessThanOrEqual(255, mb_strlen($request['reason']));

            return true;
        });
    }
}
