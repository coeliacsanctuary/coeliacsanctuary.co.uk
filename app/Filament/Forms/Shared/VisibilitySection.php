<?php

declare(strict_types=1);

namespace App\Filament\Forms\Shared;

use App\Models\Blogs\Blog;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class VisibilitySection
{
    public static function make(): Section
    {
        return Section::make('Visibility')
            ->schema([
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'live' => 'Live',
                        'scheduled' => 'Scheduled',
                    ])
                    ->formatStateUsing(function (Blog $record): string {
                        if ($record->live) {
                            return 'live';
                        }

                        if ($record->publish_at) {
                            return 'scheduled';
                        }

                        return 'draft';
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        if ($state === 'live') {
                            $set('publish_at', null);
                        }
                    })
                    ->default('draft'),

                DateTimePicker::make('publish_at')
                    ->visible(fn (Get $get): bool => $get('status') === 'scheduled')
                    ->required(fn (Get $get): bool => $get('status') === 'scheduled'),
            ]);
    }
}
