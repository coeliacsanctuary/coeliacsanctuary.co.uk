<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Enums\EatingOut\EateryType;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Filament\Schemas\Components\EateryLocationSection;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryVenueType;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class EateryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make(fn (CreateEatery $livewire): string => $livewire->recommendationAiData()?->is_eligible ? '✓ Eligible for adding' : '✗ Not eligible')
                    ->description(fn (CreateEatery $livewire): ?HtmlString => $livewire->recommendationAiData()?->explanation ? str($livewire->recommendationAiData()->explanation)->markdown(['html_input' => 'escape'])->toHtmlString() : null)
                    ->status(fn (CreateEatery $livewire): string => $livewire->recommendationAiData()?->is_eligible ? 'success' : 'danger')
                    ->visible(fn ($livewire): bool => $livewire instanceof CreateEatery && $livewire->recommendationAiData()?->completed_at !== null)
                    ->key('recommendation_eligibility')
                    ->columnSpanFull(),

                Grid::make()
                    ->columns(5)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Introduction')
                            ->columnSpan(4)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(200),

                                Hidden::make('unlock_slug')
                                    ->default(false)
                                    ->dehydrated(false),

                                TextInput::make('slug')
                                    ->visible(fn (string $operation): bool => $operation === 'edit')
                                    ->readOnly(fn (Get $get): bool => ! $get('unlock_slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Eatery $record) => $rule->where('town_id', $record->town_id))
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

                                Toggle::make('closed_down')
                                    ->hidden(fn (string $operation): bool => $operation === 'create')
                                    ->helperText('If a location has closed down, then as long as it is still live then it will be removed from lists and maps, but the page will still load for search engines.'),
                            ]),
                    ]),

                EateryLocationSection::make(),

                Section::make('Contact Details')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->nullable()
                            ->maxLength(50),

                        TextInput::make('website')
                            ->url()
                            ->nullable()
                            ->maxLength(255),

                        TextInput::make('gf_menu_link')
                            ->label('GF Menu Link')
                            ->url()
                            ->nullable()
                            ->maxLength(255),

                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->nullable()
                            ->maxLength(255),

                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->nullable()
                            ->maxLength(255),
                    ]),

                Section::make('Details')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type_id')
                            ->relationship('type', 'name', fn (Builder $query) => $query->reorder('id'))
                            ->columnSpanFull()
                            ->default(EateryType::EATERY->value)
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                $type = (int) $state;

                                $set('venue_type_id', $type === EateryType::HOTEL->value ? 26 : null);
                                $set('cuisine_id', $type === EateryType::EATERY->value ? null : 29);

                                if ($type === EateryType::ATTRACTION->value) {
                                    $set('info', null);
                                }
                            }),

                        Grid::make()
                            ->visible(fn (Get $get): bool => $get('type_id') !== null)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('venue_type_id')
                                    ->relationship('venueType', 'venue_type', function (Builder $query, Get $get): Builder {
                                        /** @var Builder<EateryVenueType> $query */
                                        return $query->where('type_id', (int) $get('type_id'))->reorder('venue_type');
                                    })
                                    ->disabled(fn (Get $get): bool => (int) $get('type_id') === EateryType::HOTEL->value)
                                    ->dehydrated()
                                    ->required(),

                                Select::make('cuisine_id')
                                    ->relationship('cuisine', 'cuisine', fn (Builder $query) => $query->reorder('cuisine'))
                                    ->hidden(fn (Get $get): bool => (int) $get('type_id') !== EateryType::EATERY->value)
                                    ->dehydratedWhenHidden()
                                    ->required(fn (Get $get): bool => (int) $get('type_id') === EateryType::EATERY->value),

                                Textarea::make('info')
                                    ->columnSpanFull()
                                    ->rows(5)
                                    ->hidden(fn (Get $get): bool => (int) $get('type_id') === EateryType::ATTRACTION->value)
                                    ->dehydratedWhenHidden()
                                    ->required(fn (Get $get): bool => (int) $get('type_id') !== EateryType::ATTRACTION->value),

                                Textarea::make('snippet')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->nullable()
                                    ->maxLength(150)
                                    ->helperText('A short summary shown on eatery cards and search results, leave blank to fall back to a truncated version of the info above.'),

                                Repeater::make('restaurants')
                                    ->columnSpanFull()
                                    ->relationship()
                                    ->visible(fn (Get $get): bool => (int) $get('type_id') === EateryType::ATTRACTION->value)
                                    ->schema([
                                        TextInput::make('restaurant_name')->dehydrateStateUsing(fn (?string $state): string => $state ?: ''),

                                        Textarea::make('info')->rows(3)->required(),
                                    ]),

                                EateryOpeningTimes::make()
                                    ->columnSpanFull()
                                    ->hidden(fn (Get $get): bool => (int) $get('type_id') !== EateryType::EATERY->value),
                            ]),
                    ]),

                Section::make('Features')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        CheckboxList::make('features')
                            ->hiddenLabel()
                            ->relationship(titleAttribute: 'feature')
                            ->bulkToggleable()
                            ->columns(4),
                    ]),
            ]);
    }
}
