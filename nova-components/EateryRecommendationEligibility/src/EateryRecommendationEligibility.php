<?php

declare(strict_types=1);

namespace Jpeters8889\EateryRecommendationEligibility;

use Laravel\Nova\Fields\Field;

class EateryRecommendationEligibility extends Field
{
    public $component = 'eatery-recommendation-eligibility';

    public function __construct(bool $isEligible, string $explanation)
    {
        parent::__construct('AI Eligibility', 'ai_eligibility_display');

        $this->withMeta([
            'isEligible' => $isEligible,
            'explanation' => $explanation,
        ])->readonly();
    }

    public function fillModelWithData(object $model, mixed $value, string $attribute): void
    {
        //
    }
}
