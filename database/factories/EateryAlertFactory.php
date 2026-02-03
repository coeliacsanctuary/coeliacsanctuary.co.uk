<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;

class EateryAlertFactory extends Factory
{
    protected $model = EateryAlert::class;

    public function definition(): array
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'type' => 'website',
            'details' => '',
            'completed' => false,
            'ignored' => false,
        ];
    }

    public function websiteAlert(): self
    {
        return $this->state(fn () => [
            'type' => 'website',
        ]);
    }

    public function googleAlert(): self
    {
        return $this->state(fn () => [
            'type' => 'google_places',
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'completed' => true,
        ]);
    }

    public function ignored(): self
    {
        return $this->state(fn () => [
            'ignored' => true,
        ]);
    }

    public function on(Eatery|int $eatery): self
    {
        return $this->state(fn () => [
            'wheretoeat_id' => $eatery instanceof Eatery ? $eatery->id : $eatery,
        ]);
    }
}
