<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'wheretoeat_types')]
class EateryType extends Model
{
    public const EATERY = 1;

    public const ATTRACTION = 2;

    public const HOTEL = 3;

    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'type_id');
    }
}
