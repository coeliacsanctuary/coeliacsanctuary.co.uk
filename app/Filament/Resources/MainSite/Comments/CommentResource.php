<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Comments\Tables\CommentsTable;
use App\Models\Comments\Comment;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends BaseResource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['commentable', 'reply'])->reorder('id', 'desc');
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Comment::query()->where('approved', false)->count();

        if ($count > 0) {
            return (string) $count;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
