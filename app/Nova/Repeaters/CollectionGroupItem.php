<?php

declare(strict_types=1);

namespace App\Nova\Repeaters;

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Jpeters8889\CollectionItemSearch\CollectionItemSearch;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class CollectionGroupItem extends Repeatable
{
    public static $model = \App\Models\Collections\CollectionGroupItem::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Type', 'item_type')
                ->fullWidth()
                ->stacked()
                ->displayUsingLabels()
                ->options([
                    Blog::class => 'Blog',
                    Recipe::class => 'Recipe',
                    Eatery::class => 'Eatery',
                    NationwideBranch::class => 'Nationwide Branch',
                ]),

            CollectionItemSearch::make('Item', 'item_id')
                ->fullWidth()
                ->stacked(),

            Text::make('Title', 'item_title')
                ->fullWidth()
                ->stacked()
                ->nullable()
                ->help('Leave blank to use the items title'),

            Textarea::make('Description', 'item_description')
                ->fullWidth()
                ->stacked()
                ->nullable()
                ->help('Leave blank to use the items description'),
        ];
    }
}
