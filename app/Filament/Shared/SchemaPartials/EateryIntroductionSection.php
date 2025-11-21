<?php

declare(strict_types=1);

namespace App\Filament\Shared\SchemaPartials;

use App\Models\EatingOut\Eatery;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rules\Unique;

class EateryIntroductionSection
{
    public static function make(array $extra = [], bool $isBranch = false): Section
    {
        return Section::make('Introduction')
            ->columnSpanFull()
            ->schema([
                TextInput::make('name')
                    ->required($isBranch === false)
                    ->dehydrateStateUsing($isBranch ? fn (?string $state): string => $state ?: '' : null),

                TextInput::make('slug')
                    ->visible(fn (string $operation) => $operation === 'edit')
                    ->unique(modifyRuleUsing: fn (Unique $rule, Eatery $record) => $rule->where('town_id', $record->town_id), ignoreRecord: true)
                    ->required(),

                Toggle::make('live'),

                ...$extra,
            ]);
    }
}
