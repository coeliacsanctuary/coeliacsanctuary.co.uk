<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\SealiacOverview;
use App\Nova\Actions\EatingOut\InvalidateSealiacOverview;
use App\Nova\Resource;
use App\Nova\Resources\EatingOut\Eateries;
use App\Nova\Resources\EatingOut\NationwideBranches;
use App\Nova\Resources\Shop\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
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

            MorphTo::make('Model')->types([
                Eateries::class,
                NationwideBranches::class,
                Products::class,
            ]),

            Text::make('Overview')->displayUsing(fn () => Str::limit($this->resource->overview, 100))->onlyOnIndex(),

            Textarea::make('Overview'),

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
}
