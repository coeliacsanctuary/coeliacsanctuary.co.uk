<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Throwable;

/** @extends resource<ShopOrderItem> */
/**
 * @codeCoverageIgnore
 */
class OrderItem extends Resource
{
    /** @var class-string<ShopOrderItem> */
    public static string $model = ShopOrderItem::class;

    public static $perPageViaRelationship = 10;

    public static $searchable = false;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Hidden::make('Old Product Id', 'product_id'),

            Hidden::make('old_variant_id', 'product_variant_id'),

            Image::make('Image', function (ShopOrderItem $item) {
                try {
                    return $item->product?->main_image;
                } catch (Throwable) {
                    return null;
                }
            })
                ->disk('media')
                ->preview(fn ($value) => $value)
                ->thumbnail(fn ($value) => $value)
                ->indexWidth(92),

            BelongsTo::make('Product', resource: Products::class)
                ->help('Note, only products in the same category are shown')
                ->readonly(function (NovaRequest $request) {
                    if ($request instanceof ResourceIndexRequest) {
                        return true;
                    }

                    $item = ShopOrderItem::query()->find($request->resourceId);

                    $editableStates = [
                        OrderState::PAID,
                        OrderState::READY,
                    ];

                    if ( ! in_array($item->order->state_id, $editableStates)) {
                        return true;
                    }
                })
                ->relatableQueryUsing(function (NovaRequest $request, Builder $builder) {
                    if ($request instanceof ResourceIndexRequest) {
                        return $builder;
                    }

                    $item = ShopOrderItem::query()
                        ->with(['product', 'product.categories'])
                        ->find($request->resourceId);

                    return $builder->whereHas('categories', fn (Builder $builder) => $builder->whereIn('category_id', $item->product->categories->pluck('id')));
                }),

            BelongsTo::make('Variant', resource: ProductVariant::class)
                ->canSee(function (NovaRequest $request) {
                    $item = ShopOrderItem::query()->find($request->resourceId);

                    return $item?->product?->variants()->first()->title !== '';
                })
                ->readonly(function (NovaRequest $request) {
                    if ($request instanceof ResourceIndexRequest) {
                        return true;
                    }

                    $item = ShopOrderItem::query()->find($request->resourceId);

                    $editableStates = [
                        OrderState::PAID,
                        OrderState::READY,
                    ];

                    return ! in_array($item->order->state_id, $editableStates);
                })
                ->relatableQueryUsing(function (NovaRequest $request, Builder $builder) {
                    if ($request instanceof ResourceIndexRequest) {
                        return true;
                    }

                    $item = ShopOrderItem::query()->findOrFail($request->resourceId);

                    return $builder->where('product_id', $item->product_id);
                }),

            Number::make('Quantity')->readonly(),

            Currency::make('Line Price', 'product_price')->asMinorUnits()->readonly(),

            Currency::make('Subtotal', fn (ShopOrderItem $item) => $item->product_price * $item->quantity)->asMinorUnits(),
        ];
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        $newProduct = ShopProduct::query()->find($request->input('product'));

        $oldVariant = ShopProductVariant::query()->find($request->input('product_variant_id'));
        $newVariant = $request->has('variant') ? ShopProductVariant::query()->find($request->input('variant')) : $newProduct->variants->first();

        if ($oldVariant->id !== $newVariant->id) {
            $model->update([
                'product_title' => $newProduct->title,
                'product_variant_id' => $newVariant->id,
            ]);

            $oldVariant->increment('quantity', $request->integer('quantity'));
            $newVariant->decrement('quantity', $request->integer('quantity'));
        }
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return "resources/orders/{$resource->order_id}";
    }

    public static function indexQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query)
    {
        return $query->with(['product' => fn ($query) => $query->withoutGlobalScopes(), 'variant' => fn ($query) => $query->withoutGlobalScopes(), 'product.media']);
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
        return true;
    }
}
