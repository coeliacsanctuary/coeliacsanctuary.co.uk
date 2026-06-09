<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopPostageCountry;
use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<ShopPostageCountry> */
class PostageCountry extends Resource
{
    /** @var class-string<ShopPostageCountry> */
    public static string $model = ShopPostageCountry::class;

    protected $perPage = 50;

    public static $title = 'country';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            BelongsTo::make('Postage Area', 'area', PostageArea::class)->filterable()->readonly(),

            Text::make('Country'),

            Text::make('ISO Code'),
        ];
    }

    public function authorizedToAdd(NovaRequest $request, $model): bool
    {
        return true;
    }
}
