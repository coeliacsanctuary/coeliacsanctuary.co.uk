<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Contracts\Comments\HasComments;
use App\Models\OpenGraphImage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpenGraphImage>
 */
class OpenGraphImageFactory extends Factory
{
    protected $model = OpenGraphImage::class;

    public function definition()
    {
        return [
            'model_id' => null,
            'model_type' => null,
            'route' => null,
        ];
    }

    public function on(HasComments $commentable)
    {
        return $this->state(fn (array $attributes): array => [
            'commentable_type' => $commentable::class,
            'commentable_id' => $commentable->id,
        ]);
    }
}
