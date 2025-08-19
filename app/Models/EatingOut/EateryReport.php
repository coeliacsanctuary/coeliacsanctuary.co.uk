<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EateryReport extends Model
{
    protected $table = 'wheretoeat_place_reports';

    protected $casts = [
        'completed' => 'bool',
        'ignored' => 'bool',
    ];

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }

    /** @return BelongsTo<NationwideBranch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(NationwideBranch::class, 'branch_id');
    }
}
