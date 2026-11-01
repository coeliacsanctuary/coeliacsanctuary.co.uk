<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blogs\BlogTag;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blogs\BlogTag>
 */
class BlogTagFactory extends Factory
{
    protected $model = BlogTag::class;

    public function definition()
    {
        return [
            'tag' => $this->faker->word(),
            'slug' => $this->faker->slug(),
        ];
    }
}
