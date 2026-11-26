<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts\Actions;

use App\Models\EatingOut\EateryAlert;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IgnoreAlertAction
{
    public static function make(): Action
    {
        return Action::make('ignore')
            ->label('Ignore')
            ->icon(Heroicon::OutlinedBellSlash)
            ->color('warning')
            ->requiresConfirmation()
            ->disabled(fn (EateryAlert $record): bool => $record->completed || $record->ignored)
            ->action(function (EateryAlert $record): void {
                $record->update(['ignored' => true]);

                Notification::make()
                    ->title('Alert ignored')
                    ->success()
                    ->send();
            });
    }
}
