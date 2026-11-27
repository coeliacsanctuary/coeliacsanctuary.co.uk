<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports\Actions;

use App\Models\EatingOut\EateryReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IgnoreReportAction
{
    public static function make(): Action
    {
        return Action::make('ignore')
            ->label('Ignore')
            ->icon(Heroicon::OutlinedBellSlash)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (EateryReport $record): bool => ! $record->completed && ! $record->ignored)
            ->action(function (EateryReport $record): void {
                $record->update(['ignored' => true]);

                Notification::make()
                    ->title('Report ignored')
                    ->success()
                    ->send();
            });
    }
}
