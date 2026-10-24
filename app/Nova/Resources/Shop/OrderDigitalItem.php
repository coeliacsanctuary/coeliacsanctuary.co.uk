<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopOrderDigitalItem;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<ShopOrderDigitalItem> */
class OrderDigitalItem extends Resource
{
    /** @var class-string<ShopOrderDigitalItem> */
    public static string $model = ShopOrderDigitalItem::class;

    public static $perPageViaRelationship = 10;

    public static $searchable = false;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            BelongsTo::make('Card', resource: DigitalCardResource::class),

            Currency::make('Subtotal', fn (ShopOrderDigitalItem $item) => $item->digital_card_price)->asMinorUnits(),
        ];
    }

    public static function indexQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query)
    {
        return $query->with(['card' => fn ($query) => $query->withoutGlobalScopes()]);
    }

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }
}
