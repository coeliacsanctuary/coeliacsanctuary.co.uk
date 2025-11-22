<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports;

use App\Filament\Resources\EatingOut\EateryReports\Pages\ListEateryReports;
use App\Filament\Resources\EatingOut\EateryReports\Tables\EateryReportsTable;
use App\Models\EatingOut\EateryReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EateryReportResource extends Resource
{
    protected static ?string $model = EateryReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function table(Table $table): Table
    {
        return EateryReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryReports::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = EateryReport::query()->withoutGlobalScopes()->where('completed', false)->where('ignored', false)->count();

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
