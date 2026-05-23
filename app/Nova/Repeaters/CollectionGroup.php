<?php

declare(strict_types=1);

namespace App\Nova\Repeaters;

use App\Nova\FieldOverrides\Repeater;
use App\Nova\Resources\Main\CollectionGroupItem;
use Jpeters8889\Body\Body;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CollectionGroup extends Repeatable
{
    public static $model = \App\Models\Collections\CollectionGroup::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Title')
                ->fullWidth()
                ->stacked()
                ->nullable(),

            Body::make('Body')
                ->onlyOnForms()
                ->fullWidth()
                ->stacked()
                ->noToolbar()
                ->nullable(),

            Repeater::make('Items', 'items')
                ->repeatables([
                    \App\Nova\Repeaters\CollectionGroupItem::make(),
                ])
                ->fullWidth()
                ->asHasMany(CollectionGroupItem::class),
        ];
    }
}
