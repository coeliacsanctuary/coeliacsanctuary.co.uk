<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReviewInvitation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopOrderReviewInvitation>
 */
class ShopOrderReviewInvitationFactory extends Factory
{
    protected $model = ShopOrderReviewInvitation::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'sent' => false,
            'order_id' => self::factoryForModel(ShopOrder::class),
            'sent_at' => null,
        ];
    }

    public function forOrder(ShopOrder $order): self
    {
        return $this->state(fn (): array => [
            'order_id' => $order->id,
        ]);
    }
}
