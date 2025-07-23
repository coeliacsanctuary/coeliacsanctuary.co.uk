<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SealiacOverview extends Model
{
    /** @return MorphTo<Model, $this> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'invalidated' => 'boolean',
        ];
    }
}
