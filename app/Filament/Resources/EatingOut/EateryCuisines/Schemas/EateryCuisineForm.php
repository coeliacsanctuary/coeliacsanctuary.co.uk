<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCuisines\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EateryCuisineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->contained(fn (string $operation): bool => $operation !== 'create')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('cuisine')
                            ->label('Name')
                            ->required()
                            ->maxLength(50),
                    ]),
            ]);
    }
}
