<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopPaymentResponse;

class ShopPaymentResponseFactory extends Factory
{
    protected $model = ShopPaymentResponse::class;

    public function definition()
    {
        return [
            'payment_id' => Factory::factoryForModel(ShopPayment::class),
            'charge_id' => $this->faker->uuid,
            'response' => ['foo' => 'bar'],
        ];
    }

    public function forPayment(ShopPayment $payment): self
    {
        return $this->state(fn () => [
            'payment_id' => $payment->id,
        ]);
    }
}
