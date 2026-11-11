<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table(name: 'wheretoeat_features')]
class EateryFeature extends Model
{
    /** @return BelongsToMany<Eatery, $this> */
    public function eateries(): BelongsToMany
    {
        return $this->belongsToMany(Eatery::class, 'wheretoeat_assigned_features', 'feature_id', 'wheretoeat_id');
    }
}
