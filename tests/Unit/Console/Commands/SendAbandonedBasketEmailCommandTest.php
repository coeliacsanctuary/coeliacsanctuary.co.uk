<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SendAbandonedBasketEmailCommand;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Notifications\Shop\AbandonedBasketNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class SendAbandonedBasketEmailCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    #[Test]
    public function itDoesntSendAnAbandonedCartEmailIfTheBasketIsStillOpen(): void
    {
        $this->build(ShopOrder::class)
            ->asBasket()
            ->forCustomer($this->create(ShopCustomer::class))
            ->create();

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itDoesntSendAnAbandonedCartEmailIfTheBasketIsExpiredLessThanTheTimeLimit(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(2));

        TestTime::freeze();

        $this->build(ShopOrder::class)
            ->asExpired()
            ->forCustomer($this->create(ShopCustomer::class))
            ->create();

        TestTime::addHour()->addMinutes(30);

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itDoesntSendIfTheItemHasGoneOutOfStockSinceTheBasketWasCreated(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(1));

        TestTime::freeze();

        $this->withCategoriesAndProducts(1, 1);

        $variant = ShopProductVariant::query()->first();

        $basket = $this->build(ShopOrder::class)
            ->forCustomer($this->create(ShopCustomer::class))
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        TestTime::addHour();

        $variant->update(['quantity' => 0]);

        TestTime::addMinute();

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itNotifiesTheCustomerIfTheAllTheCriteriaIsMet(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(1));

        TestTime::freeze();

        $this->withCategoriesAndProducts(1, 1);

        $variant = ShopProductVariant::query()->first();

        $customer = $this->create(ShopCustomer::class);

        $basket = $this->build(ShopOrder::class)
            ->forCustomer($customer)
            ->asExpired()
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        TestTime::addHour()->addMinute();

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertSentTo($customer, AbandonedBasketNotification::class);
    }

    #[Test]
    public function itUpdatesTheBasketToShowTheAbandonedBasketEmailHasBeenSent(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(1));

        TestTime::freeze();

        $this->withCategoriesAndProducts(1, 1);

        $variant = ShopProductVariant::query()->first();

        $customer = $this->create(ShopCustomer::class);

        $basket = $this->build(ShopOrder::class)
            ->forCustomer($customer)
            ->asExpired()
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        TestTime::addHour()->addMinute();

        $this->assertFalse($basket->sent_abandoned_basket_email);

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertSentTo($customer, AbandonedBasketNotification::class);

        $this->assertTrue($basket->refresh()->sent_abandoned_basket_email);
    }

    #[Test]
    public function itDoesntSendIfTheFlagIsSet(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(1));

        TestTime::freeze();

        $this->withCategoriesAndProducts(1, 1);

        $variant = ShopProductVariant::query()->first();

        $customer = $this->create(ShopCustomer::class);

        $basket = $this->build(ShopOrder::class)
            ->forCustomer($customer)
            ->asExpired()
            ->beenSentAbandonedBasketEmail()
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        TestTime::addHour()->addMinute();

        $this->assertTrue($basket->sent_abandoned_basket_email);

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itDoesntSendForOldBaskets(): void
    {
        Config::set('coeliac.shop.abandoned_basket_time_limit', fn(Carbon $carbon) => Carbon::now()->subHours(1));

        TestTime::freeze();

        $this->withCategoriesAndProducts(1, 1);

        $variant = ShopProductVariant::query()->first();

        $customer = $this->create(ShopCustomer::class);

        $basket = $this->build(ShopOrder::class)
            ->forCustomer($customer)
            ->asExpired()
            ->create();

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        TestTime::addDay();

        $this->artisan(SendAbandonedBasketEmailCommand::class);

        Notification::assertNothingSent();
    }
}
