<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Tables;

use App\Filament\Fields\Status\Table\StatusColumn;
use App\Models\Recipes\Recipe;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),

                TextColumn::make('title')->searchable(),

                StatusColumn::make(),

                TextColumn::make('created_at')->dateTime(),

                TextColumn::make('publish_at')->dateTime(),
            ])
            ->recordActions([
                Action::make('view')
                    ->visible(fn (Recipe $record) => $record->live)
                    ->icon(Heroicon::Eye)
                    ->label('View')
                    ->url(fn (Recipe $record) => $record->absolute_link)
                    ->openUrlInNewTab(),

                EditAction::make(),
            ]);
    }
}
