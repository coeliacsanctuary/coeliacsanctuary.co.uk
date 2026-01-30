<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCheck;

class EateryCheckFactory extends Factory
{
    protected $model = EateryCheck::class;

    public function definition(): array
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'website_checked_at' => null,
            'google_checked_at' => null,
        ];
    }

    public function checked(): self
    {
        return $this->state(fn () => [
            'website_checked_at' => now(),
            'google_checked_at' => now(),
        ]);
    }

    public function websiteChecked(): self
    {
        return $this->state(fn () => [
            'website_checked_at' => now(),
        ]);
    }

    public function googleChecked(): self
    {
        return $this->state(fn () => [
            'google_checked_at' => now(),
        ]);
    }
}
