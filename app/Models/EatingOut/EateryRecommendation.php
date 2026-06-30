<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EateryRecommendation extends Model
{
    protected $table = 'wheretoeat_place_recommendation';

    protected $casts = [
        'completed' => 'bool',
        'ignored' => 'bool',
    ];

    /** @return HasOne<EateryRecommendationAiData, $this> */
    public function aiData(): HasOne
    {
        return $this->hasOne(EateryRecommendationAiData::class, 'wheretoeat_place_recommendation_id');
    }

    /** @return BelongsTo<EateryVenueType, $this> */
    public function venueType(): BelongsTo
    {
        return $this->belongsTo(EateryVenueType::class, 'place_venue_type_id');
    }
}
