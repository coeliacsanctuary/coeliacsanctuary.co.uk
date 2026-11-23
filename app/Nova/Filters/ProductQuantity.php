<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Models\Shop\ShopProduct;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ProductQuantity extends Filter
{
    /**
     * @var string
     */
    public $component = 'select-filter';

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ShopProduct>  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->whereRelation('variants', 'quantity', '<', $value);
    }

    /**
     * @return array
     */
    public function options(NovaRequest $request)
    {
        return [
            '< 5' => 5,
            '< 10' => 10,
            '< 20' => 20,
        ];
    }
}
