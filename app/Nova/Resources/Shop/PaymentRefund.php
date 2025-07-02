<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopPaymentRefund> */
/**
 * @codeCoverageIgnore
 */
class PaymentRefund extends Resource
{
    /** @var class-string<ShopPaymentRefund> */
    public static string $model = ShopPaymentRefund::class;

    public static $clickAction = 'view';

    public static $searchable = false;

    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            Currency::make('Amount')->asMinorUnits(),
            Text::make('Note'),
            DateTime::make('Refund Date', 'created_at'),
        ];
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Currency::make('Amount')->asMinorUnits(),

            Textarea::make('Note')->rows(2)->alwaysShow(),

            Text::make('Stripe Refund ID', 'refund_id')->readonly()->default(''),

            Text::make('Payment ID')
                ->showOnCreating()
                ->readonly()
                ->default(function (NovaRequest $request) {
                    if ($request->get('viaResourceId')) {
                        $order = ShopOrder::query()->find($request->get('viaResourceId'));

                        return $order?->payment?->id;
                    }

                    return null;
                })
                ->readonly(),

            DateTime::make('Refund Date', 'created_at'),
        ];
    }

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }
}
