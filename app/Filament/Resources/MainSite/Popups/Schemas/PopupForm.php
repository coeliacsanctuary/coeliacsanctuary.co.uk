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
                    ->columns(2)
                    ->schema([
                        TextInput::make('text')->required(),

                        TextInput::make('link')->required(),

                        TextInput::make('display_every')->numeric()->suffix('Days')->required(),

                        Toggle::make('live')->inline(false),

                        SpatieMediaLibraryFileUpload::make('primary')
                            ->label('Primary Image (Horizontal)')
                            ->hiddenLabel()
                            ->collection('primary')
                            ->imagePreviewHeight('176px'),

                        SpatieMediaLibraryFileUpload::make('secondary')
                            ->label('Secondary Image (Vertical)')
                            ->hiddenLabel()
                            ->collection('primary')
                            ->imagePreviewHeight('176px'),
                    ]),
            ]);
    }
}
