<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopCustomsFee;
use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<ShopCustomsFee> */
class CustomsFee extends Resource
{
    /** @var class-string<ShopCustomsFee> */
    public static string $model = ShopCustomsFee::class;

    protected $perPage = 50;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            BelongsTo::make('Postage Country', 'country', PostageCountry::class),

            Currency::make('Fee')
                ->asMinorUnits()
                ->rules(['required']),

            Text::make('Description')->nullable()->maxlength(255),
        ];
    }

    public function authorizedToAdd(NovaRequest $request, $model): bool
    {
        return true;
    }
}
