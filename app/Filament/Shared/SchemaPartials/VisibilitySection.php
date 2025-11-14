<?php

declare(strict_types=1);

namespace App\Filament\Shared\SchemaPartials;

use App\Filament\Fields\Status\Form\StatusField;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
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
                StatusField::make(),

                DateTimePicker::make('publish_at')
                    ->visible(fn (Get $get): bool => $get('status') === 'scheduled')
                    ->required(fn (Get $get): bool => $get('status') === 'scheduled'),
            ]);
    }
}
