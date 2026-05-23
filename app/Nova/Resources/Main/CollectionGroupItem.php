<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionGroupItem as CollectionGroupItemModel;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

/** @extends resource<CollectionGroupItemModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionGroupItem extends Resource
{
    //    use HasSortableRows;

    public static string $model = CollectionGroupItemModel::class;

    public static $perPageViaRelationship = 200;

    public static $with = ['item'];

    public function fields(NovaRequest $request)
    {
        return [
            //
        ];
    }

    //    protected static function fillFields(NovaRequest $request, $model, $fields): array
    //    {
    //        $fillFields = parent::fillFields($request, $model, $fields);
    //        /** @var CollectionGroupModel $item */
    //        $item = $fillFields[0];
    //
    //        $item->description = $item->item->meta_description;
    //
    //        return $fillFields;
    //    }

    //    public static function afterCreate(NovaRequest $request, Model $model): void
    //    {
    //        /** @var CollectionGroupModel $model */
    //        $model->collection->touch();
    //    }
    //
    //    public static function afterUpdate(NovaRequest $request, Model $model): void
    //    {
    //        /** @var CollectionGroupModel $model */
    //        $model->collection->touch();
    //    }

    //    public function actions(NovaRequest $request)
    //    {
    //        return [Action::using('', fn() => null)];
    //    }
}
