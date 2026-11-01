<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Nova\FieldOverrides\Stack;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @extends Resource<ShopOrderReview>
 *
 * @codeCoverageIgnore
 */
class OrderReviews extends Resource
{
    /** @var class-string<ShopOrderReview> */
    public static string $model = ShopOrderReview::class;

    public static $clickAction = 'view';

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name'),

            Stack::make(
                'Reviewed Products',
                fn (ShopOrderReview $resource) => $resource
                    ->products()
                    ->with(['product' => fn (Relation $relation) => $relation->withoutGlobalScopes()])
                    ->get()
                    ->map(fn (ShopOrderReviewItem $item): array => [
                        Line::make('Product', fn () => $item?->product->title)->extraClasses('font-bold'),
                        Line::make('Rating', fn (): string => "{$item->rating} stars")->extraClasses(match ((string) $item->rating) {
                            '5' => 'text-green-700',
                            '4' => 'text-yellow-500',
                            '3' => 'text-blue-700',
                            '2' => 'text-grey-800',
                            default => 'text-red-700',
                        }),
                        Line::make('Review', fn () => Str::limit($item->review, 150)),
                    ]),
            )
                ->processUsing(fn (Collection $collection) => $collection->when(fn (Collection $items): bool => $items->count() > 1, fn (Collection $items) => $items->map(fn (array $lines, $index): array => [
                    $lines[0],
                    $lines[1],
                    $lines[2]->extraClasses($index < $items->count() - 1 ? 'inline-block border-b border-gray-300 pb-2 mb-2' : ''),
                ])))
                ->onlyOnIndex(),

            Date::make('Created At')->exceptOnForms(),

            HasMany::make('Reviewed Items', 'products', OrderReviewItem::class),
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
}
