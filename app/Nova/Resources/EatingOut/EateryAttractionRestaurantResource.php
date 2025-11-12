<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class EateryAttractionRestaurantResource extends Resource
{
    public static $model = EateryAttractionRestaurant::class;

    public static $title = 'id';

    public static $search = [
        'id', 'restaurant_name', 'info',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Restaurant Name'),

            Text::make('Info'),

            BelongsTo::make('Eatery', 'eatery', Eateries::class),
        ];
    }
}
