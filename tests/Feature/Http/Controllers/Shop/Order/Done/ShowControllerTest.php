<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Order\Done;

use App\Actions\Shop\GetPaymentIntentAction;
use App\Actions\Shop\GetStripeChargeAction;
use App\Actions\Shop\MarkOrderAsPaidAction;
use App\Enums\Shop\OrderState;
use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    use MocksStripe;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->build(ShopOrder::class)
            ->asPending()
            ->has($this->build(ShopPayment::class), 'payment')
            ->create();

        Event::fake();
    }

    #[Test]
    public function itReturnsAnErrorIfItIsMissingAPaymentIntent(): void
    {
        $this->get(route('shop.basket.done'))
            ->assertSessionHasErrors('payment_intent')
            ->assertRedirectToRoute('shop.index');
    }

    #[Test]
    public function itReturnsAnErrorIfItIsMissingAPaymentIntentSecret(): void
    {
        $this->get(route('shop.basket.done'))
            ->assertSessionHasErrors('payment_intent_client_secret')
            ->assertRedirectToRoute('shop.index');
    }

    #[Test]
    public function itErrorsIfItCantFindABasketWithThePaymentIntent(): void
    {
        $this
            ->get(route('shop.basket.done', [
                'payment_intent' => 'foo',
                'payment_intent_client_secret' => 'bar',
            ]))
            ->assertRedirectToRoute('shop.index');
    }

    #[Test]
    public function itErrorsIfTheMatchingBasketIsntAPendingOrder(): void
    {
        $this->order->update(['state_id' => OrderState::PAID]);

        $this
            ->get(route('shop.basket.done', [
                'payment_intent' => 'foo',
                'payment_intent_client_secret' => $this->order->payment_intent_secret,
            ]))
            ->assertRedirectToRoute('shop.index');
    }

    #[Test]
    public function itCallsTheGetPaymentIntentAction(): void
    {
        $this->mockRetrieveCharge('bar');

        $this->expectAction(
            GetPaymentIntentAction::class,
            return: $this->createPaymentIntent(['status' => PaymentIntent::STATUS_SUCCEEDED, 'latest_charge' => 'bar']),
        );

        $this->get(route('shop.basket.done', [
            'payment_intent' => 'foo',
            'payment_intent_client_secret' => $this->order->payment_intent_secret,
        ]));
    }

    #[Test]
    public function itReturnsBackToTheBasketPageIfTheStatusIsNotSucceeded(): void
    {
        $this->mockRetrievePaymentIntent('foo', PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD);

        $this
            ->get(route('shop.basket.done', [
                'payment_intent' => 'foo',
                'payment_intent_client_secret' => $this->order->payment_intent_secret,
            ]))
            ->assertRedirectToRoute('shop.basket.checkout')
            ->withCookie('basket_token', $this->order->token);
    }

    #[Test]
    public function itCallsTheGetStripeChargeAction(): void
    {
        $this->mockRetrievePaymentIntent('foo', params: ['latest_charge' => 'bar']);

        $this->expectAction(
            GetStripeChargeAction::class,
            return: $this->createCharge('bar'),
        );

        $this->get(route('shop.basket.done', [
            'payment_intent' => 'foo',
            'payment_intent_client_secret' => $this->order->payment_intent_secret,
        ]));
    }

    #[Test]
    public function itCallsTheMarkOrderAsPaidAction(): void
    {
        $this->mockRetrievePaymentIntent('foo', params: ['latest_charge' => 'bar']);
        $this->mockRetrieveCharge('bar');

        $this->expectAction(MarkOrderAsPaidAction::class, [ShopOrder::class, Charge::class]);

        $this->get(route('shop.basket.done', [
            'payment_intent' => 'foo',
            'payment_intent_client_secret' => $this->order->payment_intent_secret,
        ]));
    }

    #[Test]
    public function itDispatchesTheOrderPaidEvent(): void
    {
        $this->mockRetrievePaymentIntent('foo', params: ['latest_charge' => 'bar']);
        $this->mockRetrieveCharge('bar');

        $this->get(route('shop.basket.done', [
            'payment_intent' => 'foo',
            'payment_intent_client_secret' => $this->order->payment_intent_secret,
        ]));

        Event::assertDispatched(OrderPaidEvent::class);
    }

    #[Test]
    public function itReturnsTheInertiaResponse(): void
    {
        $this->mockRetrievePaymentIntent('foo', params: ['latest_charge' => 'bar']);
        $this->mockRetrieveCharge('bar');

        $this->get(route('shop.basket.done', [
            'payment_intent' => 'foo',
            'payment_intent_client_secret' => $this->order->payment_intent_secret,
        ]))->assertInertia(fn (Assert $page) => $page->component('Shop/OrderComplete'));
    }
}
