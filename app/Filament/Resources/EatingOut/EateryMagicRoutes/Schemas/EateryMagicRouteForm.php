<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Schemas;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class EateryMagicRouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->schema([
                    Select::make('resolver_type')
                        ->label('Magic Route Type')
                        ->options(EateryMagicRouteType::class)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::predictEateries($get, $set)),

                    MorphToSelect::make('location')
                        ->required()
                        ->searchable()
                        ->live()
                        ->types(fn (Get $get): array => self::locationTypes(self::resolverType($get)))
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::predictEateries($get, $set)),

                    TextInput::make('predicted_eateries')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated(false),
                ]),
        ]);
    }

    /** @return array<int, Type> */
    protected static function locationTypes(?EateryMagicRouteType $resolverType): array
    {
        $types = [
            Type::make(EateryCounty::class)
                ->titleAttribute('county')
                ->modifyOptionsQueryUsing(function (Builder $query): Builder {
                    /** @var Builder<EateryCounty> $query */
                    return $query->with('country');
                })
                ->getOptionLabelFromRecordUsing(fn (EateryCounty $record): string => EateryMagicRouteResource::countyLabel($record)),

            Type::make(EateryTown::class)->titleAttribute('town'),
        ];

        if ($resolverType === EateryMagicRouteType::HundredPercentGlutenFree) {
            return $types;
        }

        return [
            ...$types,
            Type::make(EateryArea::class)->titleAttribute('area'),
        ];
    }

    protected static function predictEateries(Get $get, Set $set): void
    {
        $resolverType = self::resolverType($get);
        $locationType = $get('location_type');
        $locationId = $get('location_id');

        if ( ! $resolverType instanceof EateryMagicRouteType || ! $locationType || ! $locationId) {
            $set('predicted_eateries', null);

            return;
        }

        $key = match ($locationType) {
            EateryCounty::class => 'county_id',
            EateryTown::class => 'town_id',
            EateryArea::class => 'area_id',
            default => throw new RuntimeException('Unsupported location type'),
        };

        $configuration = new Configuration([new Where("[parent].{$key}", '=', $locationId)]);

        if ($resolverType->builderConfiguration()) {
            $resolverType->builderConfiguration()($configuration);
        }

        $set('predicted_eateries', app(GetEateriesForMagicRoutePipeline::class)->run($configuration)->count());
    }

    protected static function resolverType(Get $get): ?EateryMagicRouteType
    {
        $resolverType = $get('resolver_type');

        if ($resolverType instanceof EateryMagicRouteType || $resolverType === null) {
            return $resolverType;
        }

        return EateryMagicRouteType::tryFrom($resolverType);
    }
}
