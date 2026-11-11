<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'wheretoeat_searches')]
class EaterySearch extends Model
{
    /** @return BelongsTo<EaterySearchTerm, $this> */
    public function term(): BelongsTo
    {
        return $this->belongsTo(EaterySearchTerm::class, 'search_term_id');
    }
}
