<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Actions\EatingOut\CheckEateryRecommendationWithAiAction;
use App\Models\EatingOut\EateryRecommendation;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class CheckWithAiAction
{
    public static function make(): Action
    {
        return Action::make('checkWithAi')
            ->label('Check with AI')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (EateryRecommendation $record): bool => $record->aiData === null)
            ->action(function (EateryRecommendation $record, CheckEateryRecommendationWithAiAction $checkWithAi): void {
                $checkWithAi->handle($record);

                Notification::make()
                    ->title('Eatery Recommendation is queued to be checked with AI.')
                    ->success()
                    ->send();
            });
    }
}
