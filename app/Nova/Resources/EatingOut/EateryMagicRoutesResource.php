<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Actions\EatingOut\MagicRouting\GenerateNewMagicRouteAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Nova\Actions\EatingOut\RegenerateMagicRouteBody;
use App\Nova\Resource;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class EateryMagicRoutesResource extends Resource
{
    public static $model = EateryMagicRouteRecord::class;

    public static $title = 'id';

    public function fields(Request $request): array
    {
        return [
            ID::make(),

            Select::make('Magic Route Type', 'resolver_type')
                ->options(EateryMagicRouteType::class),

            MorphTo::make('Location')
                ->dependsOn('resolver_type', function (MorphTo $field, NovaRequest $request, FormData $data) {
                    $option = $data->get('resolver_type');

                    if ($option === EateryMagicRouteType::HundredPercentGlutenFree->value) {
                        return $field->types([
                            Counties::class,
                            Towns::class,
                        ]);
                    }

                    return $field->types([
                        Counties::class,
                        Towns::class,
                        Areas::class,
                    ]);
                }),

            Number::make('Eateries', fn(EateryMagicRouteRecord $model) => app(GetEateriesForMagicRoutePipeline::class)->run($model->builder_config)->count())
                ->readonly()
                ->exceptOnForms(),

            Number::make('Predicted Eateries')
                ->readonly()
                ->onlyOnForms()
                ->dependsOn(['location', 'resolver_type', 'location_type'], function (Number $field, NovaRequest $request, FormData $data) {
                    if(!$data->get('resolver_type') || !$data->get('location_type') || !$data->get('location')) {
                        return;
                    }

                    $resolverType = EateryMagicRouteType::from($data->get('resolver_type'));
                    $locationType = Str::singular($data->get('location_type'));
                    $location = $data->get('location');

                    $config = new Configuration([new Where("[parent].{$locationType}_id", '=', $location)]);

                    if ($resolverType->builderConfiguration()) {
                        $resolverType->builderConfiguration()($config);
                    }

                    $eateries = app(GetEateriesForMagicRoutePipeline::class)->run($config)->count();

                    $field->setValue($eateries);
                }),

            KeyValue::make('Content', 'body')
                ->hideFromIndex()
                ->readonly()
                ->exceptOnForms()
                ->rules('json'),

            Code::make('Configuration', 'builder_config')
                ->hideFromIndex()
                ->exceptOnForms()
                ->resolveUsing(function ($foo, ?EateryMagicRouteRecord $resource) {
                    return json_encode(json_decode($resource->getRawOriginal('builder_config'), true), JSON_PRETTY_PRINT);
                })
                ->readonly()
                ->language('javascript'),
        ];
    }

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function actions(NovaRequest $request)
    {
        return [
            RegenerateMagicRouteBody::make()->sole(),
        ];
    }

    public static function beforeCreate(NovaRequest $request, Model $model)
    {
        /** @var EateryMagicRouteRecord $model */

        $resource = app(GenerateNewMagicRouteAction::class)->handle($model->resolver_type, $model->location);

        $model->fill($resource->withoutRelations()->toArray());
        $model->syncOriginal();
        $model->exists = true;
    }
}
