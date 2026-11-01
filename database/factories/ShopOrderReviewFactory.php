<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewInvitation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopOrderReview>
 */
class ShopOrderReviewFactory extends Factory
{
    protected $model = ShopOrderReview::class;

    public function definition()
    {
        return [
            'order_id' => self::factoryForModel(ShopOrder::class),
            'invitation_id' => self::factoryForModel(ShopOrderReviewInvitation::class),
            'name' => $this->faker->name,
        ];
    }

    public function forOrder(ShopOrder $order): self
    {
        return $this->state(fn (): array => [
            'order_id' => $order->id,
        ]);
    }

    public function fromInvitation(ShopOrderReviewInvitation $invitation): self
    {
        return $this->state(fn (): array => [
            'invitation_id' => $invitation->id,
        ]);
    }
}
