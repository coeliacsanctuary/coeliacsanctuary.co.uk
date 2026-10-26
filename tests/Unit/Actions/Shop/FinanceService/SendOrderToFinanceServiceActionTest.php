<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\FinanceService;

use App\Actions\Shop\FinanceService\SendOrderToFinanceServiceAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopDiscountCodesUsed;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendOrderToFinanceServiceActionTest extends TestCase
{
    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.finance.url' => 'https://finance.test',
            'services.finance.key' => 'secret-key',
        ]);

        $this->order = $this->build(ShopOrder::class)->asPaid()->create();

        Http::preventStrayRequests();
        Http::fake(['https://finance.test/api/v1/orders' => Http::response()]);
    }

    #[Test]
    public function itPostsTheOrderToTheFinanceServiceWithTheApiKey(): void
    {
        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://finance.test/api/v1/orders'
            && $request->hasHeader('Authorization', 'Bearer secret-key'));
    }

    #[Test]
    public function itSendsTheOrderDetails(): void
    {
        $this->order->payment->update(['subtotal' => 1500, 'postage' => 250, 'total' => 1750]);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertEquals($this->order->token, $request['order_id']);
            $this->assertEquals($this->order->created_at->toIso8601String(), $request['ordered_at']);
            $this->assertEquals($this->order->payment->response->charge_id, $request['stripe_charge_id']);
            $this->assertEquals(1500, $request['subtotal']);
            $this->assertEquals(250, $request['postage']);
            $this->assertEquals(1750, $request['total']);

            return true;
        });
    }

    #[Test]
    public function itSendsTheCustomerDetails(): void
    {
        $this->order->address->update([
            'line_1' => '1 Test Street',
            'line_2' => 'Test Area',
            'line_3' => 'Test Village',
            'town' => 'Crewe',
            'county' => 'Cheshire',
            'postcode' => 'CW1 1AA',
            'country' => 'United Kingdom',
        ]);

        $this->order->customer->update(['name' => 'Alison Peters', 'email' => 'alison@example.com']);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertEquals([
                'name' => 'Alison Peters',
                'email' => 'alison@example.com',
                'address_1' => '1 Test Street',
                'address_2' => 'Test Area',
                'address_3' => 'Test Village',
                'town' => 'Crewe',
                'county' => 'Cheshire',
                'postcode' => 'CW1 1AA',
                'country' => 'United Kingdom',
            ], $request['customer']);

            return true;
        });
    }

    #[Test]
    public function itOnlySendsTheCustomerNameAndEmailForADigitalOnlyOrder(): void
    {
        $order = $this->build(ShopOrder::class)
            ->asPaid()
            ->create(['is_digital_only' => true, 'shipping_address_id' => null]);

        $order->customer->update(['name' => 'Alison Peters', 'email' => 'alison@example.com']);

        $this->callAction(SendOrderToFinanceServiceAction::class, $order);

        Http::assertSent(function (Request $request) {
            $this->assertSame([
                'name' => 'Alison Peters',
                'email' => 'alison@example.com',
            ], $request['customer']);

            return true;
        });
    }

    #[Test]
    public function itSendsThePhysicalItemsWithTheTotalPriceForTheQuantity(): void
    {
        $item = $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->create(['product_title' => 'Travel Card', 'product_price' => 450, 'quantity' => 3]);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) use ($item) {
            $this->assertEquals([[
                'id' => $item->product_id,
                'variant_id' => $item->product_variant_id,
                'name' => 'Travel Card',
                'quantity' => 3,
                'line_price' => 450,
                'total_price' => 1350,
            ]], $request['items']);

            return true;
        });
    }

    #[Test]
    public function itSendsTheDigitalItemsAsASingleQuantityWithNoVariant(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->forCard($card)
            ->create(['digital_card_title' => 'Digital Travel Card', 'digital_card_price' => 300]);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) use ($card) {
            $this->assertEquals([[
                'id' => $card->id,
                'variant_id' => null,
                'name' => 'Digital Travel Card',
                'quantity' => 1,
                'line_price' => 300,
                'total_price' => 300,
            ]], $request['items']);

            return true;
        });
    }

    #[Test]
    public function itSendsBothPhysicalAndDigitalItemsTogether(): void
    {
        $this->build(ShopOrderItem::class)->inOrder($this->order)->create();
        $this->build(ShopOrderDigitalItem::class)->inOrder($this->order)->create();

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertCount(2, $request['items']);

            return true;
        });
    }

    #[Test]
    public function itSendsTheFees(): void
    {
        $this->order->payment->update(['fees_breakdown' => [
            ['fee' => 150, 'description' => 'Customs Fee'],
            ['fee' => 50, 'description' => 'Handling Fee'],
        ]]);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertEquals([
                ['name' => 'Customs Fee', 'amount' => 150],
                ['name' => 'Handling Fee', 'amount' => 50],
            ], $request['fees']);

            return true;
        });
    }

    #[Test]
    public function itSendsAnEmptyFeesArrayWhenThereAreNoFees(): void
    {
        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertSame([], $request['fees']);

            return true;
        });
    }

    #[Test]
    public function itSendsNullDiscountsWhenNoDiscountCodeWasUsed(): void
    {
        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertNull($request['discounts']);

            return true;
        });
    }

    #[Test]
    public function itSendsTheDiscountCodeWhenOneWasUsed(): void
    {
        $discountCode = $this->create(ShopDiscountCode::class, ['name' => 'Ten Percent Off', 'code' => 'ten-off']);

        $this->build(ShopDiscountCodesUsed::class)
            ->forOrder($this->order)
            ->forDiscountCode($discountCode)
            ->create();

        $this->order->payment->update(['discount' => 175]);

        $this->callAction(SendOrderToFinanceServiceAction::class, $this->order);

        Http::assertSent(function (Request $request) {
            $this->assertEquals([[
                'name' => 'Ten Percent Off',
                'code' => 'ten-off',
                'amount' => 175,
            ]], $request['discounts']);

            return true;
        });
    }
}
