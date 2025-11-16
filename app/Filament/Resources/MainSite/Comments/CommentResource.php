<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Comments;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Comments\Tables\CommentsTable;
use App\Models\Comments\Comment;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommentResource extends BaseResource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

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
