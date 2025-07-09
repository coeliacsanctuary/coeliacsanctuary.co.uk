<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\SealiacOverview;
use App\Nova\Actions\EatingOut\InvalidateSealiacOverview;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class SealiacOverviews extends Resource
{
    public static $model = SealiacOverview::class;

    public static $searchable = false;

    public static $clickAction = 'open';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->hide(),

            Text::make('Overview')->displayUsing(function () {
                if (app(NovaRequest::class)->filled('viaResourceId')) {
                    return Str::limit($this->resource->overview);
                }

                return nl2br($this->resource->overview);
            })->asHtml(),

            Badge::make('Status', 'invalidated')
                ->map([
                    false => 'success',
                    true => 'danger',
                ])
                ->labels([
                    false => 'Active',
                    true => 'Invalidated',
                ]),

            Number::make('Thumbs Up Count', 'thumbs_up'),

            Number::make('Thumbs Down Count', 'thumbs_down'),

            Number::make('Rating', '')->displayUsing(fn () => $this->resource->thumbs_up - $this->resource->thumbs_down),

            BelongsTo::make('Eatery', resource: Eateries::class),

            BelongsTo::make('Branch', resource: NationwideBranches::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            InvalidateSealiacOverview::make()
                ->sole()
                ->showInline()
                ->canRun(fn ($foo, SealiacOverview $sealiacOverview) => $sealiacOverview->invalidated === false)
                ->confirmText('Are you sure you want to invalidate this overview?'),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->with(['eatery']);
    }
}
