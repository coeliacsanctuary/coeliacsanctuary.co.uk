<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SealiacOverview extends Model
{
    protected $table = 'wheretoeat_sealiac_overview';

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }

    /** @return BelongsTo<NationwideBranch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(NationwideBranch::class, 'nationwide_branch_id');
    }

    protected function casts(): array
    {
        return [
            'invalidated' => 'boolean',
        ];
    }
}
