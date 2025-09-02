<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\EatingOut\Eatery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EateryAiDescription extends Model
{
    protected $table = 'wheretoeat_ai_descriptions';

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }
}
