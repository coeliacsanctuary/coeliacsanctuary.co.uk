<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryReviews\Pages\ListEateryReviews;
use App\Filament\Resources\EatingOut\EateryReviews\Tables\EateryReviewsTable;
use App\Models\EatingOut\EateryReview;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EateryReviewResource extends BaseResource
{
    protected static ?string $model = EateryReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function table(Table $table): Table
    {
        return EateryReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryReviews::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = EateryReview::query()->withoutGlobalScopes()->where('approved', false)->count();

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
