<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'wheretoeat_place_recommendations_ai_data')]
class EateryRecommendationAiData extends Model
{
    protected $casts = [
        'is_eligible' => 'bool',
        'features' => 'array',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /** @return BelongsTo<EateryRecommendation, $this> */
    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(EateryRecommendation::class, 'wheretoeat_place_recommendation_id');
    }
}
