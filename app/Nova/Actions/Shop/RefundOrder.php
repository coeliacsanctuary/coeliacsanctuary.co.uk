<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class RefundOrder extends DestructiveAction
{
    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        //
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        /** @var ShopOrder $order */
        $order = $this->resource;

        if(!$order && $request->has('resources')) {
            $order = ShopOrder::query()->find($request->get('resources')[0]);
        }

        if ( ! $order) {
            return [];
        }

        $total = number_format($order->payment->total / 100, 2);
        $subtotal = number_format($order->payment->subtotal / 100, 2);
        $postage = number_format($order->payment->postage / 100, 2);
        $fee = number_format($order->payment->fee / 100, 2);

        return [
            Select::make('Amount', 'refund_type')
                ->displayUsingLabels()
                ->options(['full' => 'Full Amount', 'partial' => 'Partial'])
                ->default('full')
                ->fullWidth(),

            Currency::make('', 'amount')
                ->asMinorUnits()
                ->dependsOn(['refund_type'], fn(Currency $field, NovaRequest $request, FormData $data) => $field->readonly($data->get('refund_type') === 'full'))
                ->default($total)
                ->max($total)
                ->fullWidth()
                ->help("Subtotal: £{$subtotal} / Postage: £{$postage} / Fee: £{$fee}"),

            Boolean::make('Cancel Order', 'cancel')
                ->readonly($order->state_id >= OrderState::SHIPPED)
                ->fullWidth(),

            Boolean::make('Notify Customer', 'notify')
                ->dependsOn(['cancel'], function(Boolean $field, NovaRequest $request, FormData $data) {
                    if($data->boolean('cancel')) {
                        $field->setValue(false);
                        $field->help('Customer will be automatically notified when canceling an order');
                    }

                    $field->readonly($data->boolean('cancel'));
                })
                ->fullWidth(),
        ];
    }
}
