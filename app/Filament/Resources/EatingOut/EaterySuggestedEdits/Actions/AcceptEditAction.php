<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions;

use App\Models\EatingOut\EaterySuggestedEdit;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AcceptEditAction
{
    public static function make(): Action
    {
        return Action::make('accept')
            ->label('Accept')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (EaterySuggestedEdit $record): bool => ! $record->accepted && ! $record->rejected)
            ->action(function (EaterySuggestedEdit $record): void {
                $record->update(['accepted' => true]);

                Notification::make()
                    ->title('Suggested edit accepted')
                    ->success()
                    ->send();
            });
    }
}
