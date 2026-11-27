<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Support\EatingOut\SuggestEdits\SuggestedEditProcessor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AcceptAndImplementEditAction
{
    public static function make(): Action
    {
        return Action::make('acceptAndImplement')
            ->label('Accept and Implement')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (EaterySuggestedEdit $record): bool => ! $record->accepted && ! $record->rejected && $record->field !== 'info')
            ->action(function (EaterySuggestedEdit $record): void {
                $eatery = $record->eatery;

                assert($eatery instanceof Eatery);

                app(SuggestedEditProcessor::class)
                    ->resolveEditableField($record->field, $record->value)
                    ->commitSuggestedValue($eatery);

                $record->update(['accepted' => true]);

                Notification::make()
                    ->title('Suggested edit accepted and implemented')
                    ->success()
                    ->send();
            });
    }
}
