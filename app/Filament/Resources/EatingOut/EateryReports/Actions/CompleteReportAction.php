<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports\Actions;

use App\Models\EatingOut\EateryReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CompleteReportAction
{
    public static function make(): Action
    {
        return Action::make('complete')
            ->label('Complete')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (EateryReport $record): bool => ! $record->completed && ! $record->ignored)
            ->action(function (EateryReport $record): void {
                $record->update(['completed' => true]);

                Notification::make()
                    ->title('Report completed')
                    ->success()
                    ->send();
            });
    }
}
