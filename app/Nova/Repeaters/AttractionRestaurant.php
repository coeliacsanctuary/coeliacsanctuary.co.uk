<?php

declare(strict_types=1);

namespace App\Nova\Repeaters;

use App\Models\EatingOut\EateryAttractionRestaurant;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttractionRestaurant extends Repeatable
{
    public static $model = EateryAttractionRestaurant::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Restaurant Name')->default('')->fillUsing(function($request, $model, $attribute, $requestAttribute) {
                $model->{$attribute} = $request->input($requestAttribute) ?: '';
            }),

            Textarea::make('Info'),
        ];
    }

    public static function label()
    {
        return 'Restaurant';
    }
}
