<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAddedSmallBusinessNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Notifications\AnonymousNotifiable;

class IgnoreAndSendSmallBusinessAction
{
    public static function make(): Action
    {
        return Action::make('ignoreAndSendSmallBusiness')
            ->label('Ignore and send added to small business blog email')
            ->icon(Heroicon::OutlinedNewspaper)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (EateryRecommendation $record): bool => ! $record->completed)
            ->action(function (EateryRecommendation $record): void {
                new AnonymousNotifiable()
                    ->route('mail', $record->email)
                    ->notify(new EateryRecommendationAddedSmallBusinessNotification($record));

                $record->update(['ignored' => true]);

                Notification::make()
                    ->title('Recommendation ignored and email sent')
                    ->success()
                    ->send();
            });
    }
}
