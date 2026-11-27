<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Models\EatingOut\EateryRecommendation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class IgnoreRecommendationAction
{
    public static function make(): Action
    {
        return Action::make('ignore')
            ->label('Ignore')
            ->icon(Heroicon::OutlinedBellSlash)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (EateryRecommendation $record): bool => ! $record->completed && ! $record->ignored)
            ->action(function (EateryRecommendation $record): void {
                $record->update(['ignored' => true]);

                Notification::make()
                    ->title('Recommendation ignored')
                    ->success()
                    ->send();
            });
    }
}
