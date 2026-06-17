<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Schemas;

use App\Filament\Forms\Components\Body;
use App\Filament\Forms\Components\BlogTagsInput;
use App\Filament\Schemas\Components\ImagesSection;
use App\Filament\Schemas\Components\MetasSection;
use App\Filament\Schemas\Components\VisibilitySection;
use App\Models\Blogs\BlogTag;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

                            TextInput::make('short_title')
                                ->maxLength(100)
                                ->nullable(),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/')
                                ->unique(ignoreRecord: true),

                            BlogTagsInput::make('tags')
                                ->required()
                                ->live()
                                ->suggestions(
                                    fn () => BlogTag::query()
                                        ->pluck('tag')
                                        ->all()
                                ),

                            Select::make('primary_tag_id')
                                ->label('Primary Tag')
                                ->options(fn (Get $get): array => BlogTag::query()
                                    ->whereIn('tag', $get('tags') ?? [])
                                    ->pluck('tag', 'id')
                                    ->all())
                                ->searchable()
                                ->helperText('If set, the primary tag will be used to find related blogs for the sidebar. Must be one of the tags above.')
                                ->nullable(),

                            Textarea::make('description')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    VisibilitySection::make()->columnSpan(1),
                ]),

            MetasSection::make()->columnSpanFull(),

            ImagesSection::make(headerImageAltText: true)->columnSpanFull(),

            Section::make('Content')
                ->columnSpanFull()
                ->schema([
                    Body::make('body')
                        ->rows(15)
                        ->validHtml()
                        ->images(),

                    Toggle::make('show_author')
                        ->label('Show Author')
                        ->default(true)
                        ->helperText('If checked, the about Alison block will be shown at the bottom of the blog.'),
                ]),

            Section::make('FAQs')
                ->columnSpanFull()
                ->collapsible()
                ->collapsed(fn (string $operation): bool => $operation !== 'create')
                ->schema([
                    Repeater::make('faqs')
                        ->schema([
                            TextInput::make('question')->required(),

                            Textarea::make('answer')->required()->rows(3),
                        ])
                        ->columnSpanFull()
                        ->addActionLabel('Add FAQ')
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->collapsible(),

                    Select::make('faq_display')
                        ->label('FAQ Position')
                        ->options([
                            'top' => 'Above content',
                            'bottom' => 'Below content',
                        ])
                        ->nullable(),
                ]),
        ]);
    }
}
