<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'wheretoeat_cuisines')]
class EateryCuisine extends Model
{
    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'cuisine_id');
    }
}
