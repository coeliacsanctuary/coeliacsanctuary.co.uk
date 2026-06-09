<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('completed', false)->where('ignored', false);
    }
}
