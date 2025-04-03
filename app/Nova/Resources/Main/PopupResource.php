<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Popup;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class PopupResource extends Resource
{
    public static $model = Popup::class;

    public static $with = ['media'];

    public static $searchable = false;

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('Text'),

            Text::make('Link'),

            Number::make('Display Every'),

            Boolean::make('Live'),

            Images::make('Primary Image', 'primary')
                ->onlyOnForms()
                ->addButtonLabel('Select Image')
                ->rules(['required']),

            Images::make('Secondary', 'secondary')
                ->help('eg Portrait images')
                ->onlyOnForms()
                ->addButtonLabel('Select Image'),
        ];
    }

    public static function label()
    {
        return 'Site Popups';
    }
}
