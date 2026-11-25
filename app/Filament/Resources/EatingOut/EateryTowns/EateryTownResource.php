<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\CreateEateryTown;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\EditEateryTown;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\ListEateryTowns;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\Schemas\EateryTownForm;
use App\Filament\Resources\EatingOut\EateryTowns\Tables\EateryTownsTable;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use BackedEnum;
use Closure;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EateryTownResource extends BaseResource
{
    protected static ?string $model = EateryTown::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'town';

    protected static ?string $recordRouteKeyName = 'id';

    protected static ?string $modelLabel = 'town';

    protected static ?string $navigationLabel = 'Towns';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('county', function (Builder $query): Builder {
                /** @var Builder<EateryCounty> $query */
                return static::constrainCounty($query->withoutGlobalScopes());
            });
    }

    /**
     * @param  Builder<EateryCounty>  $query
     * @return Builder<EateryCounty>
     */
    protected static function constrainCounty(Builder $query): Builder
    {
        return $query->where('slug', '!=', 'london');
    }

    public static function resolveRecordRouteBinding(int|string $key, ?Closure $modifyQuery = null): ?Model
    {
        $query = static::getRecordRouteBindingEloquentQuery();

        if ($modifyQuery instanceof Closure) {
            $query = $modifyQuery($query) ?? $query;
        }

        return $query->find($key);
    }

    public static function form(Schema $schema): Schema
    {
        return EateryTownForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryTownsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryTowns::route('/'),
            'create' => CreateEateryTown::route('/create'),
            'edit' => EditEateryTown::route('/{record:id}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EateriesRelationManager::class,
        ];
    }
}
