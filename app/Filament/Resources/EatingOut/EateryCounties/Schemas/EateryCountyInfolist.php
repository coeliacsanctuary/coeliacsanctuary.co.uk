<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EateryCountyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('county')->label('Name'),

                    TextEntry::make('slug'),

                    TextEntry::make('country.country')->label('Country'),

                    TextEntry::make('latlng')
                        ->label('Lat / Lng')
                        ->placeholder('-'),

                    SpatieMediaLibraryImageEntry::make('primary')
                        ->label('Image')
                        ->collection('primary')
                        ->placeholder('-'),
                ]),
        ]);
    }
}
