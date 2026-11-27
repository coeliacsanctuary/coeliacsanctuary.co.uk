<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions;

use App\Models\EatingOut\EaterySuggestedEdit;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RejectEditAction
{
    public static function make(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (EaterySuggestedEdit $record): bool => ! $record->accepted && ! $record->rejected)
            ->action(function (EaterySuggestedEdit $record): void {
                $record->update(['rejected' => true]);

                Notification::make()
                    ->title('Suggested edit rejected')
                    ->success()
                    ->send();
            });
    }
}
