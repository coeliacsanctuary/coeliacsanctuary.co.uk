<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Builders\EatingOut\EateryAlertBuilder;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseEloquentBuilder(EateryAlertBuilder::class)]
class EateryAlert extends Model
{
    protected $table = 'wheretoeat_alerts';

    protected function casts(): array
    {
        return [
            'completed' => 'bool',
            'ignored' => 'bool',
        ];
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }
}
