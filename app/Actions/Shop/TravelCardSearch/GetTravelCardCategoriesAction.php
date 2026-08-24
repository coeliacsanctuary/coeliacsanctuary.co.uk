<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\ShopCategory;
use App\Resources\Shop\ShopCategoryIndexResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetTravelCardCategoriesAction
{
    public function handle(): AnonymousResourceCollection
    {
        return ShopCategoryIndexResource::collection(
            ShopCategory::query()
                ->whereIn('id', [1, 11])
                ->with(['media', 'products' => fn (Relation $query) => $query->with('prices')])
                ->withCount('products')
                ->orderBy('id')
                ->get()
        );
    }
}
