<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'wheretoeat_place_reports')]
class EateryReport extends Model
{
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
