<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCuisines\Tables;

use App\Builders\EatingOut\EateryBuilder;
use App\Filament\Resources\EatingOut\EateryCuisines\EateryCuisineResource;
use App\Models\EatingOut\EateryCuisine;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryCuisinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryCuisine> $query */
                return $query->withCount([
                    'eateries' => fn (EateryBuilder $query): EateryBuilder => $query->where('live', true),
                ]);
            })
            ->defaultSort('cuisine')
            ->recordUrl(fn (EateryCuisine $record): string => EateryCuisineResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('cuisine')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
