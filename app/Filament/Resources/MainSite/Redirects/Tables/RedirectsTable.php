<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\Response;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from')->searchable(),

                TextColumn::make('to')->searchable(),

                TextColumn::make('status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Response::HTTP_PERMANENTLY_REDIRECT => 'Permanent',
                        Response::HTTP_TEMPORARY_REDIRECT => 'Temporary',
                        default => $state,
                    }),

                TextColumn::make('hits')->numeric()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
