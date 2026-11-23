<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use App\Builders\EatingOut\EateryAlertBuilder;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseEloquentBuilder(EateryAlertBuilder::class)]
#[Table(name: 'wheretoeat_alerts')]
class EateryAlert extends Model
{
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
