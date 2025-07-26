<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Redirect;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class RedirectResource extends Resource
{
    public static $model = Redirect::class;

    public static $title = 'id';

    public static $search = [
        'id', 'from', 'to',
    ];

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Text::make('From')
                ->rules('required'),

            Text::make('To')
                ->rules('required'),

            Select::make('Status')
                ->options([
                    Response::HTTP_PERMANENTLY_REDIRECT => 'Permanent',
                    Response::HTTP_TEMPORARY_REDIRECT => 'Temporary',
                ])
                ->displayUsingLabels()
                ->default(Response::HTTP_PERMANENTLY_REDIRECT)
                ->rules('required'),

            Number::make('Hits')->hideWhenCreating()->readonly(),
        ];
    }

    public static function label()
    {
        return 'Redirects';
    }
}
