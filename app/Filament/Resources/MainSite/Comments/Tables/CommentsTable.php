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
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Notifications\AnonymousNotifiable;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('commentable.title')
                            ->prefix(fn (Comment $record): string => "{$record->what} - ")
                            ->url(fn (Comment $record): ?string => match ($record->commentable_type) {
                                Blog::class => BlogResource::getUrl('edit', ['record' => $record->commentable_id]),
                                Recipe::class => RecipeResource::getUrl('edit', ['record' => $record->commentable_id]),
                                default => null,
                            })
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->visible(fn ($livewire): bool => ! $livewire instanceof RelationManager),

                        TextColumn::make('approved')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->alignEnd()
                            ->grow(false),
                    ]),

                    Split::make([
                        TextColumn::make('name')
                            ->icon(Heroicon::OutlinedUser)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->copyable()
                            ->grow(false),

                        TextColumn::make('created_at')
                            ->prefix('Submitted ')
                            ->dateTime()
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),
                    ])->from('md'),

                    TextColumn::make('comment')->wrap(),
                ])->space(3),

                Panel::make([
                    Stack::make([
                        TextColumn::make('reply.comment_reply')
                            ->icon(Heroicon::ArrowUturnLeft)
                            ->wrap(),

                        TextColumn::make('reply.created_at')
                            ->prefix('Replied ')
                            ->dateTime()
                            ->color('gray')
                            ->size(TextSize::Small),
                    ])->space(2),
                ])->visible(fn (?Comment $record): bool => $record?->reply !== null),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('approved'),

                SelectFilter::make('commentable_type')
                    ->label('Type')
                    ->options([
                        Blog::class => 'Blog',
                        Recipe::class => 'Recipe',
                    ])
                    ->visible(fn ($livewire): bool => ! $livewire instanceof RelationManager),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (Comment $record): bool => ! $record->approved)
                    ->action(function (Comment $record): void {
                        $record->update(['approved' => true]);

                        new AnonymousNotifiable()
                            ->route('mail', $record->email)
                            ->notify(new CommentApprovedNotification($record));
                    }),

                Action::make('reply')
                    ->label('Reply & Approve')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->schema([
                        Textarea::make('reply')->rows(5)->autosize(),
                    ])
                    ->visible(fn (Comment $record): bool => ! $record->approved)
                    ->action(function (Comment $record, array $data): void {
                        $reply = $record->reply()->create(['comment_reply' => $data['reply']]);
                        $record->update(['approved' => true]);

                        new AnonymousNotifiable()
                            ->route('mail', $record->email)
                            ->notify(new CommentRepliedNotification($reply));
                    }),

                DeleteAction::make(),
            ]);
    }
}
