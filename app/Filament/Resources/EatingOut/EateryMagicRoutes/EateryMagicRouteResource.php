<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\CreateEateryMagicRoute;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ListEateryMagicRoutes;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ViewEateryMagicRoute;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Schemas\EateryMagicRouteForm;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Schemas\EateryMagicRouteInfolist;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Tables\EateryMagicRoutesTable;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class EateryMagicRouteResource extends BaseResource
{
    protected static ?string $model = EateryMagicRouteRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'magic route';

    protected static ?string $navigationLabel = 'Magic Routes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'location' => function (Relation $relation): void {
                assert($relation instanceof MorphTo);

                $relation->morphWith([EateryCounty::class => ['country']]);
            },
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return EateryMagicRouteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EateryMagicRouteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryMagicRoutesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryMagicRoutes::route('/'),
            'create' => CreateEateryMagicRoute::route('/create'),
            'view' => ViewEateryMagicRoute::route('/{record}'),
        ];
    }

    public static function locationLabel(EateryMagicRouteRecord $record): string
    {
        return match (true) {
            $record->location instanceof EateryCounty => self::countyLabel($record->location),
            $record->location instanceof EateryTown => $record->location->town,
            $record->location instanceof EateryArea => $record->location->area,
        };
    }

    public static function countyLabel(EateryCounty $county): string
    {
        if ($county->country) {
            return "{$county->country->country} - {$county->county}";
        }

        return $county->county;
    }

    public static function eateriesCount(EateryMagicRouteRecord $record): int
    {
        return app(GetEateriesForMagicRoutePipeline::class)->run($record->builder_config)->count();
    }
}
