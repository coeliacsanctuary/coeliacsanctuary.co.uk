<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Journeys\Page;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition()
    {
        return [
            'path' => $this->faker->slug,
        ];
    }
}
