<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\CollectionGroup as CollectionGroupModel;
use App\Nova\Resource;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends resource<CollectionGroupModel> */
/**
 * @codeCoverageIgnore
 */
class CollectionGroup extends Resource
{
    public static string $model = CollectionGroupModel::class;

    public static $perPageViaRelationship = 200;

    public static $with = ['items', 'items.item'];

    public function fields(NovaRequest $request)
    {
        return [
            //
        ];
    }
}
