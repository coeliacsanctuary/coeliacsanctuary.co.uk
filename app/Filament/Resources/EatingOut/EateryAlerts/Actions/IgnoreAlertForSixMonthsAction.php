<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts\Actions;

use App\Models\EatingOut\EateryAlert;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IgnoreAlertForSixMonthsAction
{
    public static function make(): Action
    {
        return Action::make('ignoreForSixMonths')
            ->label('Ignore for 6 Months')
            ->icon(Heroicon::OutlinedClock)
            ->color('warning')
            ->requiresConfirmation()
            ->disabled(fn (EateryAlert $record): bool => $record->completed || $record->ignored)
            ->action(function (EateryAlert $record): void {
                $record->update(['ignored' => true]);

                $record->eatery->check()->updateOrCreate(
                    ['wheretoeat_id' => $record->eatery->id],
                    ['website_check_disabled_until' => now()->addMonths(6)],
                );

                Notification::make()
                    ->title('Alert ignored for 6 months')
                    ->success()
                    ->send();
            });
    }
}
