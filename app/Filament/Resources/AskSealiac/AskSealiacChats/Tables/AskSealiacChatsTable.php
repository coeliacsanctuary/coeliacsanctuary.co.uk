<?php

declare(strict_types=1);

namespace App\Filament\Resources\AskSealiac\AskSealiacChats\Tables;

use App\Filament\Resources\AskSealiac\AskSealiacChats\Actions\ChatTranscriptAction;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AskSealiacChatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('chat_id')
                    ->label('Chat')
                    ->badge()
                    ->color('gray')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(['id', 'session_id', 'chat_id']),

                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->numeric(),

                TextColumn::make('summary')
                    ->placeholder('Not yet summarised')
                    ->lineClamp(3)
                    ->wrap(),

                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                // A chat with a single message is the bot's opening greeting and nothing else.
                Filter::make('greetingOnly')
                    ->label('Greeting only')
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->has('messages', '>', 1)),
            ])
            ->recordAction('transcript')
            ->recordActions([
                ChatTranscriptAction::make(),
            ]);
    }
}
