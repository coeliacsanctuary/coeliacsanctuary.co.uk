<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Http\Response;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('from')->required(),

                        TextInput::make('to')->required(),

                        Select::make('status')
                            ->options([
                                Response::HTTP_PERMANENTLY_REDIRECT => 'Permanent',
                                Response::HTTP_TEMPORARY_REDIRECT => 'Temporary',
                            ])
                            ->default(Response::HTTP_PERMANENTLY_REDIRECT)
                            ->required(),
                    ]),
            ]);
    }
}
