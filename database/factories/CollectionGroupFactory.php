<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collections\CollectionGroup;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collections\CollectionGroup>
 */
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
        return $this->state(fn (): array => ['title' => $this->faker->sentence]);
    }

    public function withBody(): self
    {
        return $this->state(fn (): array => ['body' => $this->faker->paragraph]);
    }
}
