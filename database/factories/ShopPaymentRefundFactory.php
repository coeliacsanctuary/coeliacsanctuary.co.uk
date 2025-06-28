<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopPaymentRefund;

class ShopPaymentRefundFactory extends Factory
{
    protected $model = ShopPaymentRefund::class;

    public function definition()
    {
        return [
            'payment_id' => Factory::factoryForModel(ShopPayment::class),
            'refund_id' => $this->faker->uuid,
            'amount' => $this->faker->numberBetween(1, 10),
            'note' => $this->faker->text,
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
