<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;

class EateryReviewFactory extends Factory
{
    protected $model = EateryReview::class;

    public function definition()
    {
        return [
            'rating' => $this->faker->numberBetween(1, 5),
            'ip' => $this->faker->ipv6,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'how_expensive' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->paragraph,
            'method' => 'test',
            'approved' => false,
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'admin_review' => false,
        ];
    }

    public function approved()
    {
        return $this->state(fn (array $attributes) => ['approved' => true]);
    }

    public function adminReview()
    {
        return $this->approved()->state(fn (array $attributes) => ['admin_review' => true]);
    }

    public function on(Eatery|int $eatery)
    {
        return $this->state(fn (array $attributes) => [
            'wheretoeat_id' => $eatery instanceof Eatery ? $eatery->id : $eatery,
        ]);
    }

    public function branch(NationwideBranch|int $branch)
    {
        return $this->state(fn (array $attributes) => [
            'nationwide_branch_id' => $branch instanceof NationwideBranch ? $branch->id : $branch,
        ]);
    }
}
