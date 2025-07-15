<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Http\Response;

class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition()
    {
        return [
            'from' => $this->faker->slug,
            'to' => $this->faker->slug,
            'status' => Response::HTTP_PERMANENTLY_REDIRECT,
            'hits' => 0,
        ];

    }
}
