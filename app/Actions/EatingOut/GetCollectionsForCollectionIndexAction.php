<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\EateryCollection;
use App\ResourceCollections\EatingOut\EateryCollectionListCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetCollectionsForCollectionIndexAction
{
    /**
     * @template T of ResourceCollection
     *
     * @param  class-string<T>  $resource
     */
    public function handle(int $perPage = 12, string $resource = EateryCollectionListCollection::class, ?string $search = null): ResourceCollection
    {
        return new $resource(
            EateryCollection::query()
                ->when($search, fn (Builder $builder) => $builder->where(
                    fn (Builder $builder) => $builder
                        ->where('id', $search)
                        ->orWhere('title', 'LIKE', "%{$search}%")
                ))
                ->with(['media'])
                ->latest()
                ->paginate($perPage)
        );
    }
}
