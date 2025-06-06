<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionItem as CollectionItemModel;
use App\Nova\Resource;
use CoeliacSanctuary\NovaFieldSortable\Concerns\SortsIndexEntries;
use CoeliacSanctuary\NovaFieldSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<CollectionItemModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionItem extends Resource
{
    use SortsIndexEntries;

    public static string $defaultSortField = 'position';

    public static string $model = CollectionItemModel::class;

    public static $perPageViaRelationship = 20;

    public function fields(NovaRequest $request)
    {
        return [
            MorphTo::make('Item')->types([
                Blog::class,
                Recipe::class,
            ]),

            Sortable::make('Position')->onlyOnIndex(),
        ];
    }

    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        /** @var CollectionItemModel $model */
        $model->collection->touch();
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        /** @var CollectionItemModel $model */
        $model->collection->touch();
    }
}
