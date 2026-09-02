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
                    ->columns(2)
                    ->schema([
                        TextInput::make('from')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        TextInput::make('to')->required()->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                Response::HTTP_PERMANENTLY_REDIRECT => 'Permanent',
                                Response::HTTP_TEMPORARY_REDIRECT => 'Temporary',
                            ])
                            ->formatStateUsing(fn (mixed $state): int => match ((int) $state) {
                                Response::HTTP_FOUND, Response::HTTP_TEMPORARY_REDIRECT => Response::HTTP_TEMPORARY_REDIRECT,
                                default => Response::HTTP_PERMANENTLY_REDIRECT,
                            })
                            ->default(Response::HTTP_PERMANENTLY_REDIRECT)
                            ->required(),
                    ]),
            ]);
    }
}
