<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReportsRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\SealiacOverviewsRelationManager;
use App\Models\EatingOut\NationwideBranch;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class NationwideBranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columns(5)
                ->columnSpanFull()
                ->schema([
                    Section::make('Introduction')
                        ->columnSpan(4)
                        ->schema([
                            TextEntry::make('name')->placeholder('-'),
                        ]),

                    Section::make('Visibility')
                        ->columnSpan(1)
                        ->schema([
                            IconEntry::make('live')->boolean(),
                        ]),
                ]),

            Section::make('Location')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('full_location')->label('Location'),

                    TextEntry::make('address')
                        ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(nl2br(e($state))))
                        ->placeholder('-'),
                ]),

            Tabs::make('Panels')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Sealiac Overviews')->schema([self::panel(SealiacOverviewsRelationManager::class)]),

                    Tab::make('Reviews')->schema([self::panel(ReviewsRelationManager::class)]),

                    Tab::make('Reports')->schema([self::panel(ReportsRelationManager::class)]),
                ]),
        ]);
    }

    /** @param class-string<RelationManager> $relationManager */
    protected static function panel(string $relationManager): Livewire
    {
        return Livewire::make($relationManager, fn (NationwideBranch $record, BranchesRelationManager $livewire): array => [
            'ownerRecord' => $record,
            'pageClass' => $livewire->getPageClass(),
            ...$relationManager::getDefaultProperties(),
        ])->key(fn (NationwideBranch $record): string => "{$relationManager}-{$record->id}");
    }
}
