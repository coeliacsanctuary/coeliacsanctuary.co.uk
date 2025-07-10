<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\EatingOut\SealiacOverview;

class SealiacOverviewFactory extends Factory
{
    protected $model = SealiacOverview::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => 1,
            'nationwide_branch_id' => null,
            'overview' => $this->faker->paragraphs(3, true),
            'invalidated' => false,
            'thumbs_up' => 0,
            'thumbs_down' => 0,
        ];
    }

    public function forEatery(Eatery $eatery)
    {
        return $this->state(fn () => [
            'wheretoeat_id' => $eatery->id,
        ]);
    }

    public function forNationwideBranch(NationwideBranch $branch)
    {
        return $this->state(fn () => [
            'wheretoeat_id' => $branch->wheretoeat_id,
            'nationwide_branch_id' => $branch->id,
        ]);
    }

    public function invalidated()
    {
        return $this->state(fn () => [
            'invalidated' => true,
        ]);
    }
}
