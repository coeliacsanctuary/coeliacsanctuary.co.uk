<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'wheretoeat_checks')]
class EateryCheck extends Model
{
    protected function casts(): array
    {
        return [
            'website_checked_at' => 'datetime',
            'website_check_disabled_until' => 'datetime',
            'google_checked_at' => 'datetime',
            'disable_google_check' => 'boolean',
        ];
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }
}
