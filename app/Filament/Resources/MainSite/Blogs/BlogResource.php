<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Blogs\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MainSite\Blogs\Schemas\BlogForm;
use App\Filament\Resources\MainSite\Blogs\Tables\BlogsTable;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Blogs\Blog;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogResource extends BaseResource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->reorder('id', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return BlogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }

    public static function mutateForSave(array $data): array
    {
        return StatusTransformer::transform($data);
    }

    public static function getRelations(): array
    {
        return [CommentsRelationManager::class];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }
}
