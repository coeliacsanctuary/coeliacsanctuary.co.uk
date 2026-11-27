<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Models\EatingOut\EateryRecommendation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CompleteRecommendationAction
{
    public static function make(): Action
    {
        return Action::make('complete')
            ->label('Complete')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (EateryRecommendation $record): bool => ! $record->completed && ! $record->ignored)
            ->action(function (EateryRecommendation $record): void {
                $record->update(['completed' => true]);

                Notification::make()
                    ->title('Recommendation completed')
                    ->success()
                    ->send();
            });
    }
}
