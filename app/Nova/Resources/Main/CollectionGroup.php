<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionGroup as CollectionGroupModel;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Model;
use Jpeters8889\Body\Body;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

/** @extends resource<CollectionGroupModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionGroup extends Resource
{
    use HasSortableRows;



    public static string $model = CollectionGroupModel::class;

    public static $perPageViaRelationship = 200;

    public static $with = ['items'];

    public function fields(NovaRequest $request)
    {
        return [
            Number::make('Position'),

            Text::make('Title')
                ->nullable(),

            Body::make('Body')
                ->onlyOnForms()
                ->noToolbar()
                ->nullable(),

            Text::make('Items', fn ($model) => $model->items->count())
                ->exceptOnForms(),
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

    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        /** @var CollectionGroupModel $model */
        $model->collection->touch();
    }

    public static function afterUpdate(NovaRequest $request, Model $model): void
    {
        /** @var CollectionGroupModel $model */
        $model->collection->touch();
    }

    public function actions(NovaRequest $request)
    {
        return [Action::using('', fn() => null)];
    }
}
