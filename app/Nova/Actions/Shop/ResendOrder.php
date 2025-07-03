<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Notifications\Shop\OrderResentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jpeters8889\ShopGenerateResendSlipButton\ShopGenerateResendSlipButton;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResendOrder extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        /** @var ShopOrder $order */
        $order = $models->first();

        $overrides = $fields->collect()
            ->reject(fn ($item) => $item === null)
            ->mapwithKeys(fn ($quantity, $key) => [
                (int) Str::before($key, '-') => (int) $quantity,
            ]);

        $order->items
            ->filter(fn (ShopOrderItem $item) => $overrides->keys()->contains($item->id) && $overrides->get($item->id) > 0)
            ->each(function (ShopOrderItem $item) use ($overrides): void {
                $item->variant()->decrement('quantity', $overrides->get($item->id));
            });

        $order->customer->notify(new OrderResentNotification($order, $overrides));
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

        if ( ! $order && $request->has('resources')) {
            $order = ShopOrder::query()->find($request->get('resources')[0]);
        }

        if ( ! $order) {
            return [];
        }

        return [
            ...$order
                ->items
                ->map(fn (ShopOrderItem $item) => [
                    Heading::make($item->product_title),
                    Number::make('Quantity', "{$item->id}-quantity")
                        ->max($item->quantity)
                        ->min(0)
                        ->default($item->quantity),
                ])
                ->flatten()
                ->toArray(),

            ShopGenerateResendSlipButton::make('button')
                ->orderId($order->id)
                ->dependsOn(
                    $order->items->map(fn (ShopOrderItem $item) => "{$item->id}-quantity")->toArray(),
                    function (ShopGenerateResendSlipButton $field, NovaRequest $request, FormData $data): void {
                        $items = $data->collect()
                            ->reject(fn ($item) => $item === null)
                            ->map(fn ($item) => (int) $item)
                            ->toArray();

                        $field->withOptions($items);
                    },
                ),
        ];
    }
}
