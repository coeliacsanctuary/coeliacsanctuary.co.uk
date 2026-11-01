<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Recipes\Recipe;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    public function definition()
    {
        $title = $this->faker->sentence;
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'meta_tags' => $this->faker->paragraph,
            'meta_description' => $this->faker->paragraph,
            'description' => $this->faker->paragraph,
            'ingredients' => $this->faker->text,
            'method' => $this->faker->paragraphs(5, true),
            'author' => $this->faker->name,
            'serving_size' => '8 Slices',
            'per' => 'slice',
            'search_tags' => $this->faker->words(5, true),
            'df_to_not_df' => '',
            'prep_time' => '1 Hour',
            'cook_time' => '1 Hour',
            'live' => true,
            'publish_at' => Carbon::now(),
            'draft' => false,
        ];
    }

    public function notLive()
    {
        return $this->state(fn (): array => ['live' => false]);
    }

    public function draft()
    {
        return $this->state(fn (): array => ['draft' => true]);
    }
}
