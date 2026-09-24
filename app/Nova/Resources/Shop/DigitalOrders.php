<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Nova\Actions\Shop\RefundOrder;
use App\Nova\Metrics\ShopDailySales;
use App\Nova\Metrics\ShopIncome;
use App\Nova\Resource;
use Illuminate\Http\Request;
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

            Number::make('Items', fn (ShopOrder $order) => $order->digitalItems->count()),

            ...$request->query('viaResource') === 'discount-codes'
                ? [Currency::make('Discount', fn (ShopOrder $order) => $order->payment->discount)->asMinorUnits()]
                : [],

            Currency::make('Total', fn (ShopOrder $order) => $order->payment->total)->asMinorUnits(),

            Text::make('Payment Method', fn (ShopOrder $order) => $order->payment->payment_type_id),

            Currency::make('Processing Fee', fn (ShopOrder $order) => $order->payment->fee)->asMinorUnits(),

            HasMany::make('Refunds', resource: PaymentRefund::class),
        ];
    }

    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            Text::make('Order ID', 'order_key'),

            DateTime::make('Order Date', fn (ShopOrder $order) => $order->payment->created_at),

            HasMany::make('Digital Items', resource: OrderDigitalItem::class),

            HasOne::make('Customer', resource: Customer::class),

            HasOneThrough::make('Discount Code', resource: DiscountCode::class),

            HasOne::make('Payment', resource: Payment::class),

            HasMany::make('Refunds', resource: PaymentRefund::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            RefundOrder::make()
                ->sole()
                ->showInline()
                ->confirmText('Are you sure you want to refund this order?')
                ->confirmButtonText('Refund Order'),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            ShopDailySales::make()->digitalOnly()->refreshWhenActionsRun()->width('1/2'),
            ShopIncome::make()->digitalOnly()->refreshWhenActionsRun()->width('1/2')->help('Including Postage'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->with(['payment', 'payment.response', 'digitalItems'])
            ->withCount(['digitalItems'])
            ->where('is_digital_only', true)
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
            ->with(['payment', 'payment.response', 'digitalItems']);
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
