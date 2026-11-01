<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\Checkout;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\Checkout\CreateShippingAddressAction;
use App\DataObjects\Shop\PendingOrderShippingAddressDetails;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopShippingAddress;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateShippingAddressActionTest extends TestCase
{
    use WithFaker;

    protected ShopCustomer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);
    }

    #[Test]
    public function itCreatesAnAddress(): void
    {
        $this->assertDatabaseEmpty(ShopShippingAddress::class);

        $payload = new PendingOrderShippingAddressDetails(
            line_1: $this->faker()->streetAddress(),
            line_2: 'Foo',
            line_3: 'Bar',
            town: $this->faker->city(),
            county: $this->faker->state(),
            postcode: $this->faker->postcode(),
            country: $this->faker->country(),
        );

        app(CreateShippingAddressAction::class)->handle($this->customer, $payload);

        $this->assertDatabaseHas(ShopShippingAddress::class, [
            'name' => $this->customer->name,
            ...get_object_vars($payload),
        ]);
    }
}
