<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class ImagesSection
{
    public static function make(bool $headerImage = true, bool $socialImage = true, bool $squareImage = false, bool $headerImageAltText = false, ?callable $additionalImages = null): Section
    {
        return Section::make('Images')
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation !== 'create')
            ->schema([
                Grid::make()
                    ->columns(4)
                    ->schema(function () use ($headerImage, $socialImage, $squareImage, $headerImageAltText, $additionalImages) {
                        $schema = [];

                        if ($headerImage) {
                            $schema[] = Section::make('Header Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('header')
                                        ->hiddenLabel()
                                        ->collection('primary')
                                        ->imagePreviewHeight('176px'),

                                    ...($headerImageAltText ? [
                                        TextInput::make('header_image_alt_text')
                                            ->label('Alt Text')
                                            ->nullable(),
                                    ] : []),
                                ]);
                        }

                        if ($squareImage) {
                            $schema[] = Section::make('Square Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('square')
                                        ->hiddenLabel()
                                        ->collection('square')
                                        ->imagePreviewHeight('176px'),
                                ]);
                        }

                        if ($socialImage) {
                            $schema[] = Section::make('Social Image')
                                ->schema([
                                    Toggle::make('social_use_header_image')
                                        ->visible(fn (string $operation) => $operation === 'create')
                                        ->dehydrated(false)
                                        ->live()
                                        ->columnStart(1)
                                        ->label('Use Header Image'),

                                    SpatieMediaLibraryFileUpload::make('social')
                                        ->hidden(function (Get $get, string $operation) {
                                            if ($operation !== 'create') {
                                                return false;
                                            }

                                            return (bool) $get('social_use_header_image');
                                        })
                                        ->hiddenLabel()
                                        ->collection('social')
                                        ->columnStart(1)
                                        ->imagePreviewHeight('176px'),
                                ]);
                        }

                        if ($additionalImages) {
                            $schema[] = $additionalImages();
                        }

                        return $schema;
                    }),
            ]);
    }
}
