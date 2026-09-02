<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Tables;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\Response;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (Redirect $record) => RedirectResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),

                TextColumn::make('from')->searchable(),

                TextColumn::make('to')->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->state(fn (Redirect $record): string => match ((int) $record->status) {
                        Response::HTTP_FOUND, Response::HTTP_TEMPORARY_REDIRECT => 'Temporary',
                        default => 'Permanent',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Temporary' => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('hits')->numeric()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
