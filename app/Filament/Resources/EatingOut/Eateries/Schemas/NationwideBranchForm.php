<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Filament\Schemas\Components\EateryLocationSection;
use App\Models\EatingOut\NationwideBranch;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class NationwideBranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(5)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Introduction')
                            ->columnSpan(4)
                            ->schema([
                                TextInput::make('name')
                                    ->maxLength(200)
                                    ->dehydrateStateUsing(fn (?string $state): string => $state ?: ''),

                                Hidden::make('unlock_slug')
                                    ->default(false)
                                    ->dehydrated(false),

                                TextInput::make('slug')
                                    ->visible(fn (string $operation): bool => $operation === 'edit')
                                    ->readOnly(fn (Get $get): bool => ! $get('unlock_slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, NationwideBranch $record) => $rule->where('wheretoeat_id', $record->wheretoeat_id))
                                    ->belowContent(
                                        Action::make('unlock_slug_action')
                                            ->label('Unlock Slug')
                                            ->button()
                                            ->visible(fn (Get $get): bool => ! $get('unlock_slug'))
                                            ->action(fn (Set $set): mixed => $set('unlock_slug', true))
                                    ),
                            ]),

                        Section::make('Visibility')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('live'),
                            ]),
                    ]),

                EateryLocationSection::make(isBranch: true),
            ]);
    }
}
