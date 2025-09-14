<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Actions\SignUpToNewsletterAction;
use App\Events\Shop\OrderPaidEvent;
use App\Listeners\Shop\SubscribeToNewsletterIfRequired;
use App\Models\Shop\ShopOrder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscribeToNewsletterIfRequiredTest extends TestCase
{
    #[Test]
    public function itDoesNothingIfTheOrderIsNotSetToSubscribeToNewsletter(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create([
            'newsletter_signup' => false,
        ]);

        $event = new OrderPaidEvent($order);

        $this->mock(SignUpToNewsletterAction::class)->shouldNotReceive('handle');

        app(SubscribeToNewsletterIfRequired::class)->handle($event);
    }

    #[Test]
    public function itCallsTheSignUpToNewsletterActionIfRequiredTo(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create([
            'newsletter_signup' => true,
        ]);

        $event = new OrderPaidEvent($order);

        $this->mock(SignUpToNewsletterAction::class)
            ->shouldReceive('handle')
            ->withArgs(fn ($email) => $email === $order->customer->email)
            ->once();

        app(SubscribeToNewsletterIfRequired::class)->handle($event);
    }
}
