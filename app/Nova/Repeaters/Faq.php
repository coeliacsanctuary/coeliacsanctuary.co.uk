<?php

declare(strict_types=1);

namespace App\Nova\Repeaters;

use App\Models\Faqs\Faq as FaqModel;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Faq extends Repeatable
{
    public static $model = FaqModel::class;

    /**
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Question'),
            Textarea::make('Answer'),
        ];
    }
}
