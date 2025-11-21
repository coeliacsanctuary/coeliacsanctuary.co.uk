<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches\Tables;

use App\Models\EatingOut\NationwideBranch;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NationwideBranchesTable
{
    public static function configure(Table $table, bool $isChain = false): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes()
                ->with([
                    'eatery' => fn ($query) => $query->withoutGlobalScopes(),
                    'area' => fn ($query) => $query->withoutGlobalScopes(),
                    'town' => fn ($query) => $query->withoutGlobalScopes(),
                    'county' => fn ($query) => $query->withoutGlobalScopes(),
                    'country' => fn ($query) => $query->withoutGlobalScopes(),
                    'reviews' => fn ($query) => $query->withoutGlobalScopes(),
                ])
                ->withCount([
                    'reviews' => fn ($query) => $query->withoutGlobalScopes(),
                ])
            )
            ->groups( ! $isChain ? [
                Group::make('eatery.name')
                    ->getTitleFromRecordUsing(fn (NationwideBranch $record): string => $record->eatery->name)
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ] : [])
            ->groupingSettingsHidden()
            ->defaultGroup($isChain ? null : 'eatery.name')
            ->columns([
                TextColumn::make('eatery.name')
                    ->label('Eatery')
                    ->hidden($isChain),

                TextColumn::make('name'),

                TextColumn::make('full_location')->label('Location'),

                IconColumn::make('live')->boolean(),

                TextColumn::make('reviews_count')->label('Reviews')->numeric(),

                TextColumn::make('average_rating')
                    ->label('Average Rating')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->modalWidth(Width::SevenExtraLarge),
            ]);
    }
}
