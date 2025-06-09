<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopProduct;
use App\ResourceCollections\Shop\ShopProductApiCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetProductsForProductIndexAction
{
    /**
     * @template T of ResourceCollection
     *
     * @param  class-string<T>  $resource
     * @return T
     */
    public function handle(int $perPage = 12, string $resource = ShopProductApiCollection::class, ?string $search = null): ResourceCollection
    {
        return new $resource(
            ShopProduct::query()
                ->when($search, fn (Builder $builder) => $builder->where(
                    fn (Builder $builder) => $builder
                        ->where('id', $search)
                        ->orWhere('title', 'LIKE', "%{$search}%")
                ))
                ->with(['media', 'prices'])
                ->latest()
                ->paginate($perPage)
        );
    }
}
