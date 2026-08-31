<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

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
                                        ->required()
                                        ->collection('primary')
                                        ->imagePreviewHeight('176px'),

                                    ...($headerImageAltText ? [
                                        TextInput::make('header_image_alt_text')
                                            ->label('Alt Text')
                                            ->helperText('Descriptive alt text for the header image. Defaults to the title if left blank.')
                                            ->nullable(),
                                    ] : []),
                                ]);
                        }

                        if ($squareImage) {
                            $schema[] = Section::make('Square Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('square')
                                        ->hiddenLabel()
                                        ->required()
                                        ->collection('square')
                                        ->imagePreviewHeight('176px'),
                                ]);
                        }

                        if ($socialImage) {
                            $schema[] = Section::make('Social Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('social')
                                        ->required()
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
