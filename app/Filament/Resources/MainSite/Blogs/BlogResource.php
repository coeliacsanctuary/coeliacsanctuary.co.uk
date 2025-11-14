<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs;

use App\Filament\Resources\MainSite\Blogs\Shemas\BlogForm;
use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\MainSite\Blogs\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MainSite\Blogs\Tables\BlogsTable;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Blogs\Blog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

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
            'index' => \App\Filament\Resources\MainSite\Blogs\Pages\ListBlogs::route('/'),
            'create' => \App\Filament\Resources\MainSite\Blogs\Pages\CreateBlog::route('/create'),
            'edit' => \App\Filament\Resources\MainSite\Blogs\Pages\EditBlog::route('/{record}/edit'),
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
