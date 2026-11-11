<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\CheckIfCustomerCanAccessMyTravelCardsAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckIfCustomerCanAccessMyTravelCardsActionTest extends TestCase
{
    protected ShopCustomer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);
    }

    #[Test]
    public function itReturnsTrueIfTheCustomerHasAnOrderWithDigitalItems(): void
    {
        $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => true]);

        $this->assertTrue($this->callAction(CheckIfCustomerCanAccessMyTravelCardsAction::class, $this->customer));
    }

    #[Test]
    public function itReturnsTrueIfOnlyOneOfTheCustomersOrdersHasDigitalItems(): void
    {
        $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => false]);

        $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => true]);

        $this->assertTrue($this->callAction(CheckIfCustomerCanAccessMyTravelCardsAction::class, $this->customer));
    }

    #[Test]
    public function itReturnsFalseIfTheCustomerHasNoOrders(): void
    {
        $this->assertDatabaseEmpty(ShopOrder::class);

        $this->assertFalse($this->callAction(CheckIfCustomerCanAccessMyTravelCardsAction::class, $this->customer));
    }

    #[Test]
    public function itReturnsFalseIfNoneOfTheCustomersOrdersHaveDigitalItems(): void
    {
        $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => false]);

        $this->assertFalse($this->callAction(CheckIfCustomerCanAccessMyTravelCardsAction::class, $this->customer));
    }

    #[Test]
    public function itReturnsFalseIfAnotherCustomerHasAnOrderWithDigitalItems(): void
    {
        $this->build(ShopOrder::class)
            ->asPaid($this->create(ShopCustomer::class))
            ->create(['has_digital_items' => true]);

        $this->assertFalse($this->callAction(CheckIfCustomerCanAccessMyTravelCardsAction::class, $this->customer));
    }
}
