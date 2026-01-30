<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EateryCheck extends Model
{
    protected $table = 'wheretoeat_checks';

    protected function casts(): array
    {
        return [
            'website_checked_at' => 'datetime',
            'google_checked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }
}
