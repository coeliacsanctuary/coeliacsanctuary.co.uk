<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts\Actions;

use App\Models\EatingOut\EateryAlert;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CompleteAlertAction
{
    public static function make(): Action
    {
        return Action::make('complete')
            ->label('Complete')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->disabled(fn (EateryAlert $record): bool => $record->completed || $record->ignored)
            ->action(function (EateryAlert $record): void {
                $record->update(['completed' => true]);

                Notification::make()
                    ->title('Alert completed')
                    ->success()
                    ->send();
            });
    }
}
