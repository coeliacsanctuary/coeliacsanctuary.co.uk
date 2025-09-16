<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopPayment;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<ShopPayment> */
/**
 * @codeCoverageIgnore
 */
class Payment extends Resource
{
    /** @var class-string<ShopPayment> */
    public static string $model = ShopPayment::class;

    public static $clickAction = 'view';

    public static $search = ['id', 'name', 'email'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Currency::make('Subtotal')->asMinorUnits(),
            Currency::make('Discount')->asMinorUnits(),
            Currency::make('Postage')->asMinorUnits()->canSee(fn () => $this->model()->order->is_digital_only === false),
            Currency::make('Total')->asMinorUnits(),
            Currency::make('Fee')->asMinorUnits(),

            Text::make('Payment Type', 'payment_type_id'),

            HasMany::make('Refunds', resource: PaymentRefund::class),
        ];
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
