<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\SealiacOverviews;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\SealiacOverviews\Pages\ListSealiacOverviews;
use App\Filament\Resources\MainSite\SealiacOverviews\Tables\SealiacOverviewsTable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SealiacOverviewResource extends BaseResource
{
    protected static ?string $model = SealiacOverview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    /**
     * Every morph target is globally scoped - eateries and branches to live records,
     * products to those with variants - so without dropping those scopes per type an
     * overview for a hidden record loads with a null model.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'model' => fn (MorphTo $morphTo) => $morphTo
                ->constrain([
                    Eatery::class => fn (Builder $query) => $query->withoutGlobalScopes(),
                    NationwideBranch::class => fn (Builder $query) => $query->withoutGlobalScopes(),
                    ShopProduct::class => fn (Builder $query) => $query->withoutGlobalScopes(),
                ])
                ->morphWith([NationwideBranch::class => ['eatery']]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return SealiacOverviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSealiacOverviews::route('/'),
        ];
    }
}
