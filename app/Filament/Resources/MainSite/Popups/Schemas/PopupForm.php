<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PopupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('text')->required()->columnSpanFull(),

                        TextInput::make('link')->required()->maxLength(50),

                        TextInput::make('display_every')->numeric()->suffix('Days')->required(),

                        Toggle::make('live')->inline(false),

                        SpatieMediaLibraryFileUpload::make('primary')
                            ->label('Primary Image')
                            ->collection('primary')
                            ->image()
                            ->required()
                            ->imagePreviewHeight('176px')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('secondary')
                            ->label('Secondary Image')
                            ->helperText('eg Portrait images')
                            ->collection('secondary')
                            ->image()
                            ->imagePreviewHeight('176px')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
