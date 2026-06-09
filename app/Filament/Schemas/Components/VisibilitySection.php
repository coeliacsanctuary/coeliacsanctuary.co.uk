<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\Filament\Forms\Components\StatusField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class VisibilitySection
{
    public static function make(): Section
    {
        return Section::make('Visibility')
            ->schema([
                StatusField::make(),

                DateTimePicker::make('publish_at')
                    ->visible(fn (Get $get): bool => $get('status') === 'scheduled')
                    ->required(fn (Get $get): bool => $get('status') === 'scheduled'),
            ]);
    }
}
