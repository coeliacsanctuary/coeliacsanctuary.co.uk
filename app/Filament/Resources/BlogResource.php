<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\Blogs\BlogForm;
use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Tables\Blogs\BlogTable;
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

    protected static ?string $slug = 'blogs';

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
        return $schema
            ->components(BlogForm::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(BlogTable::make())
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->visible(fn (Blog $record) => $record->live)
                    ->icon(Heroicon::Eye)
                    ->label('View')
                    ->url(fn (Blog $record) => $record->absolute_link)
                    ->openUrlInNewTab(),

                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }
}
