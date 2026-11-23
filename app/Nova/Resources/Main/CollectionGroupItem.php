<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionGroupItem as CollectionGroupItemModel;
use App\Nova\Resource;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<CollectionGroupItemModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionGroupItem extends Resource
{
    public static string $model = CollectionGroupItemModel::class;

    public static $perPageViaRelationship = 200;

    public static $with = ['item'];

    public function fields(NovaRequest $request)
    {
        return [
        ];
    }

    //        /** @var CollectionGroupModel $item */
    //        /** @var CollectionGroupModel $model */
    //        /** @var CollectionGroupModel $model */
}
