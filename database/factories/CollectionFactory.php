<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Collections\Collection;
use Carbon\Carbon;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'meta_keywords' => implode(',', $this->faker->words(5)),
            'meta_description' => $description = $this->faker->paragraph,
            'long_description' => $description,
            'body' => $this->faker->paragraphs(3, true),
            'live' => true,
            'draft' => false,
            'display_on_homepage' => false,
            'publish_at' => Carbon::now(),
        ];
    }

    public function notOnHomepage()
    {
        return $this->state(fn () => ['display_on_homepage' => false]);
    }

    public function displayedOnHomepage(Carbon $until = null)
    {
        return $this->state(fn () => [
            'display_on_homepage' => true,
            'remove_from_homepage' => $until ?? Carbon::now()->addDays(14),
        ]);
    }
}
