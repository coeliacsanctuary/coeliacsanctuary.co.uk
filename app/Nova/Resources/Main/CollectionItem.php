<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionItem as CollectionItemModel;
use App\Nova\Resource;
use CoeliacSanctuary\NovaFieldSortable\Concerns\SortsIndexEntries;
use CoeliacSanctuary\NovaFieldSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

/** @extends Resource<CollectionItemModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionItem extends Resource
{
    use HasSortableRows;

    public static string $model = CollectionItemModel::class;

    public static $perPageViaRelationship = 200;

    public function fields(NovaRequest $request)
    {
        return [
            MorphTo::make('Item')->searchable()->types([
                Blog::class,
                Recipe::class,
            ]),

            Number::make('Position'),
        ];
    }

    public static function canSort(NovaRequest $request, $resource)
    {
        return true;
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        /** @var CollectionItemModel $item */
        $item = $fillFields[0];

        $item->description = $item->item->meta_description;

        return $fillFields;
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
