<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Tables;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Filament\Tables\Columns\StatusColumn;
use App\Models\Collections\Collection;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Collection $record) => CollectionResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),

                TextColumn::make('title')->searchable(),

                StatusColumn::make(),

                IconColumn::make('display_on_homepage')->boolean(),

                TextColumn::make('remove_from_homepage')->dateTime(),

                TextColumn::make('items_to_display')->suffix(' Items')->numeric(),

                TextColumn::make('groups_count')->label('Groups')->counts('groups'),

                TextColumn::make('items_count')->label('Items')->counts('items'),

                TextColumn::make('created_at')->dateTime(),

                TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('display_on_homepage')->label('Displayed on homepage'),
            ])
            ->recordActions([
                Action::make('view')
                    ->visible(fn (Collection $record) => $record->live)
                    ->icon(Heroicon::Eye)
                    ->label('View')
                    ->url(fn (Collection $record) => $record->absolute_link)
                    ->openUrlInNewTab(),

                EditAction::make(),
            ]);
    }
}
