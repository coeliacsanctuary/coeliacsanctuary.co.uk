<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Http\Response;

class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition(): array
    {
        return [
            'from' => '/' . $this->faker->unique()->slug(),
            'to' => '/' . $this->faker->slug(),
            'status' => Response::HTTP_PERMANENTLY_REDIRECT,
            'hits' => 0,
        ];
    }
}
