<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\DiscountCodeType;
use App\Models\Shop\ShopDiscountCode;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopDiscountCode>
 */
class ShopDiscountCodeFactory extends Factory
{
    protected $model = ShopDiscountCode::class;

    public function definition()
    {
        return [
            'name' => $name = $this->faker->words(3, true),
            'code' => Str::slug($name),
            'start_at' => Carbon::now()->startOfDay(),
            'end_at' => Carbon::now()->addWeek(),
            'max_claims' => 5,
            'min_spend' => 1,
            'deduction' => 10,
            'type_id' => DiscountCodeType::PERCENTAGE,
        ];
    }

    public function percentageDiscount(): self
    {
        return $this->state(fn (): array => [
            'type_id' => DiscountCodeType::PERCENTAGE,
        ]);
    }

    public function moneyDiscount(): self
    {
        return $this->state(fn (): array => [
            'type_id' => DiscountCodeType::MONEY,
        ]);
    }

    public function startsTomorrow()
    {
        return $this->state(fn (): array => [
            'start_at' => Carbon::tomorrow(),
        ]);
    }

    public function expired()
    {
        return $this->state(fn (): array => [
            'end_at' => Carbon::yesterday(),
        ]);
    }
}
