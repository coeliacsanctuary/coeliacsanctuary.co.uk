<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Nova\Actions\Shop\ResendDownloadLink;
use App\Nova\Metrics\ShopDailySales;
use App\Nova\Metrics\ShopIncome;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasOneThrough;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<ShopProduct> */
/**
 * @codeCoverageIgnore
 */
class DigitalOrders extends Resource
{
    /** @var class-string<ShopOrder> */
    public static string $model = ShopOrder::class;

    public static $clickAction = 'view';

    public static $search = ['id', 'order_key', 'customer.name'];

    public static $perPageViaRelationship = 10;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Order ID', 'order_key'),

            DateTime::make('Order Date', fn (ShopOrder $order) => $order->payment->created_at),

            Number::make('Items', fn (ShopOrder $order) => $order->items->sum('quantity')),

            Boolean::make('Digital Only', 'is_digital_only'),

            ...$request->query('viaResource') === 'discount-codes'
                ? [Currency::make('Discount', fn (ShopOrder $order) => $order->payment->discount)->asMinorUnits()]
                : [],

            Currency::make('Total', fn (ShopOrder $order) => $order->payment->total)->asMinorUnits(),

            Text::make('Payment Method', fn (ShopOrder $order) => $order->payment->payment_type_id),

            Currency::make('Processing Fee', fn (ShopOrder $order) => $order->payment->fee)->asMinorUnits(),

            DateTime::make('Sent At', 'digital_products_sent_at'),
        ];
    }

    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            Text::make('Order ID', 'order_key'),

            DateTime::make('Order Date', fn (ShopOrder $order) => $order->payment->created_at),

            DateTime::make('Sent At', 'digital_products_sent_at'),

            Boolean::make('Digital Only', 'is_digital_only'),

            HasOne::make('Customer', resource: Customer::class),

            HasOne::make('Payment', resource: Payment::class),

            HasOneThrough::make('Discount Code', resource: DiscountCode::class),

            HasMany::make('Items', resource: OrderItem::class),

            HasMany::make('Review', 'reviews', OrderReviews::class),

            HasMany::make('Where did you find us?', 'sources', OrderSourcesResource::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            ResendDownloadLink::make()
                ->sole()
                ->confirmButtonText('Send new Download Link'),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            ShopDailySales::make()->digitalProducts()->refreshWhenActionsRun()->width('1/2'),
            ShopIncome::make()->digitalProducts()->refreshWhenActionsRun()->width('1/2')->help('Including Postage'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->with(['payment', 'payment.response', 'items'])
            ->withCount(['items'])
            ->where('has_digital_products', true)
            ->whereNotIn('state_id', [
                OrderState::BASKET,
                OrderState::PENDING,
                OrderState::EXPIRED,
            ]);
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->with(['payment', 'payment.response', 'items']);
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
