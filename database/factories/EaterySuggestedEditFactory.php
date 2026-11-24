<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySuggestedEdit;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EaterySuggestedEdit>
 */
class EaterySuggestedEditFactory extends Factory
{
    protected $model = EaterySuggestedEdit::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'field' => 'website',
            'value' => $this->faker->url(),
            'ip' => $this->faker->ipv4(),
            'accepted' => false,
            'rejected' => false,
        ];
    }
}
