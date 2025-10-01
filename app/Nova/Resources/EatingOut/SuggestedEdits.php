<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\EatingOut\EateryVenueType;
use App\Nova\Actions\EatingOut\AcceptAndImplementEdit;
use App\Nova\Actions\EatingOut\AcceptEdit;
use App\Nova\Actions\EatingOut\RejectEdit;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class SuggestedEdits extends Resource
{
    public static $model = EaterySuggestedEdit::class;

    public static $clickAction = 'preview';

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

    public function defaultFields(): array
    {
        return [
            ID::make()->hide(),

            BelongsTo::make('Eatery', resource: Eateries::class)
                ->displayUsing(fn (Eateries $eatery) => $eatery->resource->load(['town' => fn (Relation $builder) => $builder->withoutGlobalScopes(), 'county', 'country'])->full_name),

            Badge::make('Status', function (EaterySuggestedEdit $edit) {
                if ($edit->rejected) {
                    return 'Rejected';
                }

                if ($edit->accepted) {
                    return 'Accepted';
                }

                return 'New';
            })->map([
                'Rejected' => 'danger',
                'Accepted' => 'success',
                'New' => 'info',
            ])->withIcons(),

            DateTime::make('Created', 'created_at')->showOnPreview(),

            Select::make('Field')
                ->displayUsingLabels()
                ->options(Arr::mapWithKeys(
                    array_keys(EaterySuggestedEdit::processorMaps()),
                    fn ($key) => [$key => Str::headline($key)]
                )),
        ];
    }

    public function fields(NovaRequest $request)
    {
        return [];
    }

    public function fieldsForIndex(Request $request): array
    {
        return [
            ...$this->defaultFields(),

            Text::make('Value', function (EaterySuggestedEdit $edit) {
                return match ($edit->field) {
                    'opening_times', 'features' => '',
                    default => Str::limit($edit->value, 75),
                };
            })
                ->onlyOnIndex(),
        ];
    }

    public function fieldsForDetail(Request $request): array
    {
        return $this->fieldsForPreview($request);
    }

    public function fieldsForPreview(Request $request): array
    {
        if ( ! $request->resourceId) {
            return [];
        }

        $suggestedEdit = EaterySuggestedEdit::query()
            ->with(['eatery' => fn ($query) => $query->withoutGlobalScopes()->with(['features'])])
            ->findOrFail($request->resourceId);

        $currentValue = Text::make('Current Value', function (EaterySuggestedEdit $edit) {
            return match ($edit->field) {
                'opening_times', 'features', 'info' => '',
                'venue_type' => $edit->eatery->venueType->venue_type,
                'cuisine' => $edit->eatery->cuisine?->cuisine,
                'address' => Str::replace("\n", '<br />', $edit->eatery->address),
                default => $edit->eatery->{$edit->field},
            };
        })->asHtml();

        $suggestedValue = Text::make('Suggested Value', function (EaterySuggestedEdit $edit) {
            return match ($edit->field) {
                'opening_times', 'features' => '',
                'venue_type' => EateryVenueType::query()->findOrFail($edit->value)->venue_type,
                'cuisine' => EateryCuisine::query()->findOrFail($edit->value)->cuisine,
                'address' => Str::replace("\n", '<br />', $edit->value),
                default => $edit->value,
            };
        })->asHtml();

        if ($suggestedEdit->field === 'features') {
            $value = collect(json_decode($suggestedEdit->value, true))
                ->filter(fn (array $feature) => $feature['selected'])
                ->values()
                ->map(fn (array $feature) => $feature['label']);

            $features = EateryFeature::query()->get()->mapWithKeys(fn (EateryFeature $feature) => [
                $feature->feature => false,
            ]);

            $existingFeatures = $features->map(fn (string $v, string $feature) => $suggestedEdit->eatery->features->pluck('feature')->contains($feature));
            $suggestedFeatures = $features->map(fn (string $v, string $feature) => $value->contains($feature));

            $currentValue = BooleanGroup::make('Current Value', fn () => $existingFeatures)
                ->options($features->keys()->mapWithKeys(fn ($feature) => [$feature => $feature]));

            $suggestedValue = BooleanGroup::make('Suggested Value', fn () => $suggestedFeatures)
                ->options($features->keys()->mapWithKeys(fn ($feature) => [$feature => $feature]));
        }

        if ($suggestedEdit->field === 'opening_times') {
            $value = collect(json_decode($suggestedEdit->value, true))
                ->mapWithKeys(fn (array $times) => [
                    $times['label'] => $times['closed'] ? 'Closed' : "{$times['start'][0]}:{$times['start'][1]} - {$times['end'][0]}:{$times['end'][1]}",
                ]);

            $existingTimes = Arr::mapWithKeys($suggestedEdit->eatery->openingTimes?->openingTimesArray ?? [], fn ($times, $day) => [
                Str::headline($day) => "{$times['opens']} - {$times['closes']}",
            ]);

            $currentValue = KeyValue::make('Current Value', fn () => $existingTimes);
            $suggestedValue = KeyValue::make('Suggested Value', fn () => $value);

        }

        return [
            ...$this->defaultFields(),
            $currentValue,
            $suggestedValue,
        ];

    }

    public function actions(NovaRequest $request): array
    {
        return [
            AcceptEdit::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EaterySuggestedEdit $edit) => $edit->accepted === false && $edit->rejected === false),

            AcceptAndImplementEdit::make()
                ->showInline()
                ->canRun(fn ($request, EaterySuggestedEdit $edit) => $edit->accepted === false && $edit->rejected === false && $edit->field !== 'info'),

            RejectEdit::make()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(fn ($request, EaterySuggestedEdit $edit) => $edit->accepted === false && $edit->rejected === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()->reorder('created_at', 'desc');
    }
}
