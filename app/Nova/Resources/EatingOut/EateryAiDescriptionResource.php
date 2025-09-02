<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EateryAiDescription;
use App\Nova\Actions\EatingOut\ApproveAiDescription;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class EateryAiDescriptionResource extends Resource
{
    public static $model = EateryAiDescription::class;

    public static $title = 'id';

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            BelongsTo::make('Eatery', resource: Eateries::class)->onlyOnIndex(),

            Text::make('Description')
                ->onlyOnIndex()
                ->displayUsing(fn () => "<div style=\"width: 500px; text-wrap:auto;\">{$this->resource->description}</div>")
                ->asHtml(),

            Text::make('Old Description')
                ->onlyOnIndex()
                ->displayUsing(fn () => "<div style=\"width: 500px; text-wrap:auto;\">{$this->resource->eatery->info}</div>")
                ->asHtml(),

            BelongsTo::make('Eatery', resource: Eateries::class)->hideFromIndex()->readonly(),

            Textarea::make('Description')->hideFromIndex(),
        ];
    }

    public function actions(Request $request): array
    {
        return [
            ApproveAiDescription::make()->showInline(),
        ];
    }

    public static function label()
    {
        return 'AI Eatery Descriptions';
    }

    public static function uriKey()
    {
        return 'ai-eatery-descriptions';
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query
            ->with(['eatery'])
            ->reorder('id', 'asc');
    }
}
