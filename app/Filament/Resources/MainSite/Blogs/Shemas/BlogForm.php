<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Shemas;

use App\Filament\Fields\BlogTags\Form\BlogTagsInput;
use App\Filament\Shared\SchemaPartials\ImagesSection;
use App\Filament\Shared\SchemaPartials\MetasSection;
use App\Filament\Shared\SchemaPartials\VisibilitySection;
use App\Models\Blogs\BlogTag;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columns(5)
                ->columnSpanFull()
                ->schema([
                    Section::make('Introduction')
                        ->columnSpan(4)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(200)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                    if (($get('slug') ?? '') !== Str::slug((string) $old)) {
                                        return;
                                    }

                                    $set('slug', Str::slug((string) $state));
                                })
                                ->live(),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/'),

                            BlogTagsInput::make('tags')
                                ->required()
                                ->suggestions(fn () => BlogTag::query()
                                    ->pluck('tag')
                                    ->all()
                                ),
                        ]),

                    VisibilitySection::make()->columnSpan(1),
                ]),

            MetasSection::make()->columnSpanFull(),

            ImagesSection::make(additionalImages: fn () => Section::make('Body Images')
                ->columnStart(1)
                ->columnSpanFull()
                ->schema([
                    SpatieMediaLibraryFileUpload::make('body_images')
                        ->hiddenLabel()
                        ->collection('body')
                        ->multiple()
                        ->panelLayout('grid')
                        ->itemPanelAspectRatio(0.5)
                        ->appendFiles()
                        ->imagePreviewHeight('176px'),
                ])
            )->columnSpanFull(),

            Section::make('Content')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('body')->rows(15),

//                    RichEditor::make('body')
//                        ->toolbarButtons([
//                            ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
//                            ['h2', 'h3'],
//                            ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
//                            ['table', 'attachFiles'],
//                            ['undo', 'redo'],
//                        ])
//                        ->hiddenLabel()
//                        ->required(),
                ]),
        ]);
    }
}
