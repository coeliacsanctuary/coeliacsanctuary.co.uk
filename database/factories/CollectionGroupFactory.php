<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collections\CollectionGroup;

class CollectionGroupFactory extends Factory
{
    protected $model = CollectionGroup::class;

    public function definition(): array
    {
        return [
            'title' => null,
            'body' => null,
        ];
    }

    public function withTitle(): self
    {
        return $this->state(fn () => ['title' => $this->faker->sentence]);
    }

    public function withBody(): self
    {
        return $this->state(fn () => ['body' => $this->faker->paragraph]);
    }
}
