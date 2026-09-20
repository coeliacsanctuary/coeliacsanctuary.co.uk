<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket;

use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\CheckForPendingOrderAction;
use App\Actions\Shop\CreatePaymentIntentAction;
use App\Actions\Shop\GetOrderDigitalItemsAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Enums\Shop\OrderState;
use App\Enums\Shop\PostageArea;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Resources\Shop\ShopOrderItemResource;
use App\Support\Helpers;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Testing\AssertableInertia as Assert;
use Money\Money;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Stripe\PaymentIntent;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    use MocksStripe;

    protected ShopOrder $order;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create([
            'postage_country_id' => 1,
        ]);
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_price' => 200,
        ]);

        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::UK->value,
            'shipping_method_id' => $this->product->shipping_method_id,
            'max_weight' => 500,
            'price' => 100,
        ]);
    }

    protected function addDigitalItemToBasket(int $price = 250): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->create(['digital_card_price' => $price]);

        return $item;
    }

    protected function removeThePhysicalItemsFromTheBasket(): void
    {
        $this->item->delete();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('shop.basket.checkout'))->assertOk();
    }

    #[Test]
    public function itReturnsTheInertiaCheckoutView(): void
    {
        $this->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->component('Shop/Checkout'));
    }

    #[Test]
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class);

        $this->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itCallsTheCheckForPendingOrderActionIfThereIsAPendingOrder(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create();

        $this->expectAction(CheckForPendingOrderAction::class);

        $this
            ->withCookie('basket_token', $order->token)
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itRedirectsToTheDonePageIfThePendingOrderHasAlreadyBeenPaidFor(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create();

        $this->mockRetrievePaymentIntent($order->payment_intent_secret, PaymentIntent::STATUS_SUCCEEDED);

        $this
            ->withCookie('basket_token', $order->token)
            ->get(route('shop.basket.checkout'))
            ->assertRedirect(route('shop.basket.done', [
                'payment_intent' => $order->payment_intent_id,
                'payment_intent_client_secret' => $order->payment_intent_secret,
            ]));
    }

    #[Test]
    public function itLeavesAPaidPendingOrderAlone(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create();

        $this->mockRetrievePaymentIntent($order->payment_intent_secret, PaymentIntent::STATUS_SUCCEEDED);

        $this
            ->withCookie('basket_token', $order->token)
            ->get(route('shop.basket.checkout'));

        $this->assertEquals(OrderState::PENDING, $order->refresh()->state_id);
    }

    #[Test]
    public function itPassesHasBasketFalseAsAPropIfNoBasket(): void
    {
        $this->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->where('has_basket', false));
    }

    #[Test]
    public function itPassesHasBasketFalseAsAPropIfABasketExists(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->where('has_basket', true)->etc());
    }

    #[Test]
    public function itCallsTheGetOrderItemsActonIfABasketExists(): void
    {
        $this->expectAction(GetOrderItemsAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itUpdatesTheBasketTimestamp(): void
    {
        TestTime::freeze();
        TestTime::addMinutes(30);

        $this->assertFalse($this->order->refresh()->updated_at->isSameSecond(now()));

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'));

        $this->assertTrue($this->order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itPassesTheBasketItemsToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        /** @var AnonymousResourceCollection $items */
        $items = $this->callAction(GetOrderItemsAction::class, $this->order);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->has('items', $items->count())
                            ->where('items', $items->response()->getData(true))
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheSubtotalToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        /** @var AnonymousResourceCollection $items */
        $items = $this->callAction(GetOrderItemsAction::class, $this->order);

        $subtotal = $items->collection->map(fn (ShopOrderItemResource $item) => $item->product_price * $item->quantity)->sum();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('subtotal', Helpers::formatMoney(Money::GBP($subtotal)))
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheCalculateOrderPostageAction(): void
    {
        $this->expectAction(CalculateOrderTotalsAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itCalculatesThePostagePriceAndPassesItToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('postage', '£1.00')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheTotalToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('total', '£3.00')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheFeesToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->has('fees')
                            ->has('total_fees')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheApplyDiscountCodeActionIfADiscountCodeIsPresentInTheSession(): void
    {
        $this->expectAction(ApplyDiscountCodeAction::class);

        $this->create(ShopDiscountCode::class, ['code' => 'foobar']);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->withSession(['discountCode' => app(Encrypter::class)->encrypt('foobar')])
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itPassesTheShippableCountriesToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('countries')
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheSelectedCountryId(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('selected_country', 1)
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheDeliveryTimescale(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('delivery_timescale', '1 - 2')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itCallsTheCreatePaymentIntentAction(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itCallsTheGetOrderDigitalItemsActionIfABasketExists(): void
    {
        $this->expectAction(GetOrderDigitalItemsAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'));
    }

    #[Test]
    public function itPassesTheBasketDigitalItemsToTheComponent(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $item = $this->addDigitalItemToBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->has('digital_items', 1)
                            ->where('digital_items.0.id', $item->id)
                            ->where('digital_items.0.title', $item->digital_card_title)
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesNoDigitalItemsWhenTheBasketOnlyContainsPhysicalItems(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->has('digital_items', 0)
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itFlagsTheBasketAsNotDigitalOnlyWhenItContainsAPhysicalItem(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this->addDigitalItemToBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('digital_only', false)
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itFlagsTheBasketAsDigitalOnlyWhenItOnlyContainsDigitalItems(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this->addDigitalItemToBasket();
        $this->removeThePhysicalItemsFromTheBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('digital_only', true)
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesHasBasketTrueForABasketOfOnlyDigitalItems(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this->addDigitalItemToBasket();
        $this->removeThePhysicalItemsFromTheBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->where('has_basket', true)->etc());
    }

    #[Test]
    public function itPassesHasBasketFalseForABasketWithNoItemsAtAll(): void
    {
        $this->dontExpectAction(CreatePaymentIntentAction::class);

        $this->removeThePhysicalItemsFromTheBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->where('has_basket', false)->etc());
    }

    #[Test]
    public function itIncludesTheDigitalItemsInTheBasketTotals(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this->addDigitalItemToBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('subtotal', '£4.50')
                            ->where('postage', '£1.00')
                            ->where('total', '£5.50')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntChargePostageForABasketOfOnlyDigitalItems(): void
    {
        $this->expectAction(CreatePaymentIntentAction::class);

        $this->addDigitalItemToBasket();
        $this->removeThePhysicalItemsFromTheBasket();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'basket',
                        fn (Assert $page) => $page
                            ->where('subtotal', '£2.50')
                            ->where('postage', '£0.00')
                            ->where('total', '£2.50')
                            ->etc()
                    )
                    ->etc()
            );
    }

    #[Test]
    public function itPassesTheStripePaymentToken(): void
    {
        $token = $this->mockCreatePaymentIntent(300);

        $this->withoutExceptionHandling();

        $this
            ->withCookie('basket_token', $this->order->token)
            ->get(route('shop.basket.checkout'))
            ->assertInertia(fn (Assert $page) => $page->where('payment_intent', $token)->etc());
    }
}
