<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments\Tables;

use App\Models\Comments\Comment;
use App\Notifications\CommentApprovedNotification;
use App\Notifications\CommentRepliedNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Notifications\AnonymousNotifiable;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commentable.title')
                    ->prefix(fn (Comment $record) => class_basename($record->commentable_type) . ' - ')
                    ->searchable(),

                TextColumn::make('name'),

                TextColumn::make('comment')->wrap(),

                IconColumn::make('has_reply')
                    ->getStateUsing(fn (Comment $record) => $record->reply()->exists())
                    ->boolean(),

                ToggleColumn::make('approved')
                    ->disabled(fn (bool $state) => $state)
                    ->afterStateUpdated(function (Comment $record): void {
                        (new AnonymousNotifiable())
                            ->route('mail', $record->email)
                            ->notify(new CommentApprovedNotification($record));
                    }),

                TextColumn::make('created_at')->dateTime(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('reply')
                        ->schema([
                            Textarea::make('reply')->rows(5)->autosize(),
                        ])
                        ->icon(Heroicon::ArrowUturnLeft)
                        ->action(function (Comment $record, array $data): void {
                            $reply = $record->reply()->create(['comment_reply' => $data['reply']]);
                            $record->update(['approved' => true]);

                            (new AnonymousNotifiable())
                                ->route('mail', $record->email)
                                ->notify(new CommentRepliedNotification($reply));
                        })
                        ->visible(fn (Comment $record) => ! $record->approved),

                    Action::make('view-reply')
                        ->schema([
                            TextEntry::make('reply.comment_reply')->label('Reply'),
                        ])
                        ->icon(Heroicon::Eye)
                        ->visible(fn (Comment $record) => $record->reply()->exists()),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
