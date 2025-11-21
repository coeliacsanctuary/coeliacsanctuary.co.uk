<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Comments\Tables\CommentsTable;
use App\Models\Comments\Comment;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommentResource extends BaseResource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
