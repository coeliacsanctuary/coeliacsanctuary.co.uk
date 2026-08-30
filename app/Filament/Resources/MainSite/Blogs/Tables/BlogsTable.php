<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Tables;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Tables\Columns\StatusColumn;
use App\Models\Blogs\Blog;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Blog $record) => BlogResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),

                TextColumn::make('title')->searchable(),

                TextColumn::make('primaryTag.tag')->label('Primary Tag'),

                StatusColumn::make(),

                TextColumn::make('created_at')->dateTime(),
            ])
            ->recordActions([
                Action::make('view')
                    ->visible(fn (Blog $record) => $record->live)
                    ->icon(Heroicon::Eye)
                    ->label('View')
                    ->url(fn (Blog $record) => $record->absolute_link)
                    ->openUrlInNewTab(),

                Action::make('metrics')
                    ->icon(Heroicon::ChartBar)
                    ->label('Metrics')
                    ->url(fn (Blog $record) => BlogResource::getUrl('metrics', ['record' => $record])),

                EditAction::make(),
            ]);
    }
}
