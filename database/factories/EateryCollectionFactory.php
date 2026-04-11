<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryCollection;

class EateryCollectionFactory extends Factory
{
    protected $model = EateryCollection::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'slug' => $this->faker->slug,
            'meta_tags' => $this->faker->words(5, true),
            'meta_description' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'body' => $this->faker->paragraphs(3, true),
            'draft' => false,
            'live' => true,
            'configuration' => [],
        ];
    }
}
