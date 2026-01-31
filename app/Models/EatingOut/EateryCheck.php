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
            'disable_website_check' => 'boolean',
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
