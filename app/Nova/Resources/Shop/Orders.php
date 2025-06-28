<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Nova\Actions\Shop\CancelOrder;
use App\Nova\Actions\Shop\OpenDispatchSlip;
use App\Nova\Actions\Shop\RefundOrder;
use App\Nova\Actions\Shop\ShipOrder;
use App\Nova\Metrics\ShopDailySales;
use App\Nova\Metrics\ShopIncome;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Jpeters8889\CountryIcon\CountryIcon;
use Jpeters8889\PrintAllOrders\PrintAllOrders;
use Jpeters8889\ShopOrderOpenDispatchSlip\ShopOrderOpenDispatchSlip;
use Jpeters8889\ShopOrderShippingAction\ShopOrderShippingAction;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasOneThrough;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopProduct> */
/**
 * @codeCoverageIgnore
 */
class Orders extends Resource
{
    /** @var class-string<ShopOrder> */
    public static string $model = ShopOrder::class;

    public static $clickAction = 'view';

    public static $search = ['id', 'order_key', 'customer.name', 'address.line_1', 'address.town', 'address.postcode'];

    public static $perPageViaRelationship = 10;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Order ID', 'order_key'),

            DateTime::make('Order Date', fn (ShopOrder $order) => $order->payment->created_at),

            Text::make('Address', fn (ShopOrder $order) => nl2br($order->address->formatted_address))->asHtml(),

            Number::make('Items', fn (ShopOrder $order) => $order->items->sum('quantity')),

            ...$request->query('viaResource') === 'discount-codes'
                ? [Currency::make('Discount', fn (ShopOrder $order) => $order->payment->discount)->asMinorUnits()]
                : [],

            Currency::make('Total', fn (ShopOrder $order) => $order->payment->total)->asMinorUnits(),

            Text::make('Payment Method', fn (ShopOrder $order) => $order->payment->payment_type_id),

            Currency::make('Processing Fee', fn (ShopOrder $order) => $order->payment->fee)->asMinorUnits(),

            CountryIcon::make('Country', fn (ShopOrder $order) => [
                'name' => $order->postageCountry->country,
                'code' => $order->postageCountry->iso_code,
            ]),

            ShopOrderShippingAction::make('Shipped', fn (ShopOrder $order) => [
                'parent_id' => $order->id,
                'state_id' => $order->state_id->value,
                'shipped_at' => $order->shipped_at?->format('jS M y'),
            ]),

            ShopOrderOpenDispatchSlip::make('', 'id'),
        ];
    }

    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            Text::make('Order ID', 'order_key'),

            DateTime::make('Order Date', fn (ShopOrder $order) => $order->payment->created_at),

            CountryIcon::make('Country', fn (ShopOrder $order) => [
                'name' => $order->postageCountry->country,
                'code' => $order->postageCountry->iso_code,
            ])->withLabel(),

            ShopOrderShippingAction::make('Shipped', fn (ShopOrder $order) => [
                'parent_id' => $order->id,
                'state_id' => $order->state_id->value,
                'shipped_at' => $order->shipped_at?->format('jS M y'),
            ]),

            ShopOrderOpenDispatchSlip::make('', 'id'),

            HasOne::make('Customer', resource: Customer::class),

            HasOne::make('Address', resource: ShippingAddress::class),

            HasOne::make('Payment', resource: Payment::class),

            HasOneThrough::make('Discount Code', resource: DiscountCode::class),

            HasMany::make('Items', resource: OrderItem::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            CancelOrder::make()
                ->showInline()
                ->canRun(fn ($request, ShopOrder $order) => $order->state_id !== OrderState::SHIPPED),
            RefundOrder::make()
                ->sole()
                ->showInline()
                ->canRun(fn ($request, ShopOrder $order) => $order->payment->response->charge_id !== null)
                ->confirmText('Are you sure you want to refund this order?')
                ->confirmButtonText('Refund Order'),
            OpenDispatchSlip::make()
                ->onlyInline()
                ->withoutConfirmation(),
            ShipOrder::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, ShopOrder $order) => $order->state_id === OrderState::READY),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [
            ShopDailySales::make()->refreshWhenActionsRun()->width('1/2'),
            ShopIncome::make()->refreshWhenActionsRun()->width('1/2')->help('Including Postage'),
            PrintAllOrders::make(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query
            ->withoutGlobalScopes()
            ->with(['postageCountry', 'payment', 'payment.response', 'address', 'items'])
            ->withCount(['items'])
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
            ->with(['postageCountry', 'payment', 'payment.response', 'address', 'items']);
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
