<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopShippingAddress;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ShopOrderFactory extends Factory
{
    protected $model = ShopOrder::class;

    public function definition()
    {
        return [
            'token' => Str::random(8),
        ];
    }

    public function forCustomer(?ShopCustomer $customer = null): self
    {
        return $this->state(fn () => [
            'customer_id' => $customer->id ?? static::factoryForModel(ShopCustomer::class),
        ]);
    }

    public function toAddress(?ShopShippingAddress $address = null): self
    {
        return $this->state(fn () => [
            'shipping_address_id' => $address->id ?? static::factoryForModel(ShopShippingAddress::class),
        ]);
    }

    public function asBasket(): self
    {
        return $this->state(fn () => [
            'state_id' => OrderState::BASKET,
        ]);
    }

    public function asPending(?ShopCustomer $customer = null, ?ShopShippingAddress $address = null): self
    {
        return $this
            ->forCustomer($customer)
            ->toAddress($address)
            ->has(self::factoryForModel(ShopPayment::class), 'payment')
            ->state(fn () => [
                'state_id' => OrderState::PENDING,
                'payment_intent_id' => $this->faker->password,
                'payment_intent_secret' => $this->faker->uuid,
            ]);
    }

    public function asPaid(?ShopCustomer $customer = null, ?ShopShippingAddress $address = null): self
    {
        return $this
            ->forCustomer($customer)
            ->toAddress($address)
            ->has(self::factoryForModel(ShopPayment::class), 'payment')
            ->state(fn () => [
                'state_id' => OrderState::PAID,
                'order_key' => random_int(10000000, 99999999),
            ]);
    }

    public function asShipped(?ShopCustomer $customer = null, ?ShopShippingAddress $address = null): self
    {
        return $this
            ->forCustomer($customer)
            ->toAddress($address)
            ->has(self::factoryForModel(ShopPayment::class), 'payment')
            ->state(fn () => [
                'state_id' => OrderState::SHIPPED,
                'order_key' => random_int(10000000, 99999999),
            ]);
    }

    public function asReady(): self
    {
        return $this->state(fn () => [
            'state_id' => OrderState::READY,
        ]);
    }

    public function asExpired(): self
    {
        return $this->state(fn () => [
            'state_id' => OrderState::EXPIRED,
        ]);
    }

    public function asCompleted(): self
    {
        return $this->state(fn () => [
            'state_id' => OrderState::SHIPPED,
            'order_key' => random_int(10000000, 99999999),
            'shipped_at' => Carbon::now(),
        ]);
    }
}
