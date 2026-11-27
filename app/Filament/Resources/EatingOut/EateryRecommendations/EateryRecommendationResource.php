<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Filament\Resources\EatingOut\EateryRecommendations\Tables\EateryRecommendationsTable;
use App\Models\EatingOut\EateryRecommendation;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EateryRecommendationResource extends BaseResource
{
    protected static ?string $model = EateryRecommendation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static ?string $navigationLabel = 'Place Recommendations';

    public static function table(Table $table): Table
    {
        return EateryRecommendationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryRecommendations::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = EateryRecommendation::query()
            ->where('email', '!=', 'alisondwheatley@gmail.com')
            ->where('completed', false)
            ->where('ignored', false)
            ->count();

        if ($count > 0) {
            return (string) $count;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
