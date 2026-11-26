<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryAlerts\Pages\ListEateryAlerts;
use App\Filament\Resources\EatingOut\EateryAlerts\Tables\EateryAlertsTable;
use App\Models\EatingOut\EateryAlert;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EateryAlertResource extends BaseResource
{
    protected static ?string $model = EateryAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    public static function table(Table $table): Table
    {
        return EateryAlertsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryAlerts::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = EateryAlert::query()->pending()->count();

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
