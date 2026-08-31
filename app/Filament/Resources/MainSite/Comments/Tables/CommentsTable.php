<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments\Tables;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Recipes\Recipe;
use App\Notifications\CommentApprovedNotification;
use App\Notifications\CommentRepliedNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Notifications\AnonymousNotifiable;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commentable.title')
                    ->label('Resource')
                    ->prefix(fn (Comment $record) => "{$record->what} - ")
                    ->url(fn (Comment $record) => match ($record->commentable_type) {
                        Blog::class => BlogResource::getUrl('edit', ['record' => $record->commentable_id]),
                        Recipe::class => RecipeResource::getUrl('edit', ['record' => $record->commentable_id]),
                        default => null,
                    })
                    ->visible(fn ($livewire) => ! $livewire instanceof RelationManager),

                TextColumn::make('name'),

                TextColumn::make('comment')->wrap(),

                IconColumn::make('has_reply')
                    ->state(fn (Comment $record) => $record->reply !== null)
                    ->boolean(),

                IconColumn::make('approved')->boolean(),

                TextColumn::make('created_at')->label('Added')->dateTime(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (Comment $record) => ! $record->approved)
                    ->action(function (Comment $record): void {
                        $record->update(['approved' => true]);

                        (new AnonymousNotifiable())
                            ->route('mail', $record->email)
                            ->notify(new CommentApprovedNotification($record));
                    }),

                Action::make('reply')
                    ->label('Reply & Approve')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->schema([
                        Textarea::make('reply')->rows(5)->autosize(),
                    ])
                    ->visible(fn (Comment $record) => ! $record->approved)
                    ->action(function (Comment $record, array $data): void {
                        $reply = $record->reply()->create(['comment_reply' => $data['reply']]);
                        $record->update(['approved' => true]);

                        (new AnonymousNotifiable())
                            ->route('mail', $record->email)
                            ->notify(new CommentRepliedNotification($reply));
                    }),

                Action::make('view-reply')
                    ->label('View Reply')
                    ->icon(Heroicon::Eye)
                    ->schema([
                        TextEntry::make('reply.comment_reply')->label('Reply'),
                    ])
                    ->visible(fn (Comment $record) => $record->reply !== null),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
