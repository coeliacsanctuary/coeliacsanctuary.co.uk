<?php

declare(strict_types=1);

namespace App\Models\Recipes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon $created_at
 */
class RecipeMetric extends Model
{
    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
