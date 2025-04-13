<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EateryFeature extends Model
{
    protected $table = 'wheretoeat_features';

    /** @return BelongsToMany<Eatery, $this> */
    public function eateries(): BelongsToMany
    {
        return $this->belongsToMany(Eatery::class, 'wheretoeat_assigned_features', 'feature_id', 'wheretoeat_id');
    }
}
