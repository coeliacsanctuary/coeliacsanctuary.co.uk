<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
use Illuminate\Support\Str;

class EateryReviewImageFactory extends Factory
{
    protected $model = EateryReviewImage::class;

    public function definition()
    {
        return [
            'wheretoeat_review_id' => fn (array $attributes) => self::factoryForModel(EateryReview::class)->approved()->on($attributes['wheretoeat_id']),
            'wheretoeat_id' => 1,
            'thumb' => Str::random(),
            'path' => Str::random(),
        ];
    }

    public function on(Eatery|int $eatery)
    {
        return $this->state(fn (array $attributes) => [
            'wheretoeat_id' => $eatery instanceof Eatery ? $eatery->id : $eatery,
        ]);
    }
}
