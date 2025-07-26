<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\SealiacOverview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/** @mixin Model */
trait HasSealiacOverview
{
    /** @return MorphMany<SealiacOverview, $this> */
    public function sealiacOverviews(): MorphMany
    {
        return $this->morphMany(SealiacOverview::class, 'model');
    }

    /** @return MorphOne<SealiacOverview, $this> */
    public function sealiacOverview(): MorphOne
    {
        return $this->morphOne(SealiacOverview::class, 'model')
            ->latestOfMany('created_at', 'sealiacOverviews')
            ->where('invalidated', false);
    }
}
