<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopOrderReviewItem;
use App\Nova\Resource;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @extends Resource<ShopOrderReviewItem>
 *
 * @codeCoverageIgnore
 */
class OrderReviewItem extends Resource
{
    /** @var class-string<ShopOrderReviewItem> */
    public static string $model = ShopOrderReviewItem::class;

    public static $with = ['product'];

    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Product', 'product.title')->readonly(),

            Number::make('Rating')
                ->displayUsing(fn ($value): string => $value . " Stars")
                ->min(1)
                ->max(5)
                ->required(),

            Text::make('Review', fn (ShopOrderReviewItem $item) => Str::of($item->review)->wordWrap(150, '<br/>')->toString())->asHtml()->onlyOnIndex(),

            Textarea::make('Review')->required(),
        ];
    }
}
