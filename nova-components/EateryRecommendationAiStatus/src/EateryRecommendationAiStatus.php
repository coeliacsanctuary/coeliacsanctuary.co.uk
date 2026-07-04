<?php

declare(strict_types=1);

namespace Jpeters8889\EateryRecommendationAiStatus;

use Laravel\Nova\Fields\Field;

class EateryRecommendationAiStatus extends Field
{
    public $component = 'eatery-recommendation-ai-status';

    public function resolve($resource, $attribute = null): void
    {
        $aiData = $resource->aiData;

        $this->withMeta([
            'status' => match (true) {
                $aiData === null => 'none',
                $aiData->failed_at !== null => 'failed',
                $aiData->completed_at !== null => 'completed',
                default => 'pending',
            },
            'resourceId' => $resource->id,
        ]);
    }

    public function fillModelWithData(object $model, mixed $value, string $attribute): void
    {
        //
    }
}
