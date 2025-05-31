<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Nova\Actions\Shop\CloseBasket;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Jpeters8889\CountryIcon\CountryIcon;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopProduct> */
/**
 * @codeCoverageIgnore
 */
class Baskets extends Resource
{
    /** @var class-string<ShopOrder> */
    public static string $model = ShopOrder::class;

    public static $clickAction = 'detail';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            DateTime::make('Basket Created', 'created_at'),

            DateTime::make('Last Updated', 'updated_at'),

            CountryIcon::make('Country', fn (ShopOrder $order) => [
                'name' => $order->postageCountry->country,
                'code' => $order->postageCountry->iso_code,
            ])->withLabel(),

            Number::make('Items Count'),

            Currency::make('Subtotal')->asMinorUnits(),

            Badge::make('Status', 'state_id')
                ->label(fn ($orderState) => OrderState::from($orderState)->name())
                ->map([
                    OrderState::BASKET->value => 'danger',
                    OrderState::EXPIRED->value => 'success',
                    OrderState::PENDING->value => 'info',
                ])
                ->icons([
                    'danger' => 'shopping-cart',
                    'success' => 'x-circle',
                    'info' => 'clock',
                ]),

            HasMany::make('Items', resource: OrderItem::class),

            HasOne::make('Customer', resource: Customer::class),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->select('*')
            ->selectSub('select sum(product_price * quantity) from shop_order_items i where i.order_id = shop_orders.id', 'subtotal')
            ->with(['postageCountry'])
            ->withCount(['items'])
            ->whereIn('state_id', [
                OrderState::BASKET,
                OrderState::PENDING,
                OrderState::EXPIRED,
            ]);
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->select('*')
            ->selectSub('select sum(product_price * quantity) from shop_order_items i where i.order_id = shop_orders.id', 'subtotal')
            ->with(['postageCountry'])
            ->withCount(['items'])
            ->whereIn('state_id', [
                OrderState::BASKET,
                OrderState::PENDING,
                OrderState::EXPIRED,
            ]);
    }

    public function actions(NovaRequest $request): array
    {
        return [
            CloseBasket::make()
                ->showInline()
                ->canRun(fn ($request, ShopOrder $order) => $order->state_id === OrderState::BASKET),
        ];
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }
}
