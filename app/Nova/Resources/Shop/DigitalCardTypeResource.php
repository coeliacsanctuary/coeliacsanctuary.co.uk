<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopDigitalCardType;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class DigitalCardTypeResource extends Resource
{
    public static $model = ShopDigitalCardType::class;

    public static $title = 'title';

    public static $search = [
        'id', 'title',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Title')
                ->required()
                ->fullWidth()
                ->maxlength(100),

            Slug::make('Slug')
                ->from('Title')
                ->onlyOnForms()
                ->required()
                ->fullWidth()
                ->immutable(),

            BelongsTo::make('Physical Category', 'category', Categories::class)
                ->fullWidth()
                ->help('Used to decide which physical products can be linked to a card of this type'),

            Boolean::make('Live')
                ->fullWidth(),

            Images::make('Primary Image', 'primary')
                ->addButtonLabel('Select Image')
                ->nullable(),

            Images::make('Social Image', 'social')
                ->addButtonLabel('Select Image')
                ->hideFromIndex()
                ->nullable(),
        ];
    }

    public static function indexQuery(NovaRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query)
    {
        return $query->withoutGlobalScopes();
    }

    public static function label()
    {
        return 'Digital Card Types';
    }
}
