<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Actions;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class GenerateSealiacOverviewAction
{
    public static function make(): Action
    {
        return Action::make('generateSealiacOverview')
            ->label('Generate Sealiac Overview')
            ->icon(Heroicon::Sparkles)
            ->color('gray')
            ->requiresConfirmation()
            ->visible(fn (Eatery $record): bool => $record->live === true && $record->closed_down === false && $record->reviews_count > 0)
            ->action(function (Eatery $record): void {
                $record->sealiacOverview?->update(['invalidated' => true]);

                dispatch(fn () => app(GetSealiacEateryOverviewAction::class)->handle($record));

                Notification::make()
                    ->title('Sealiac overviews are queued to be generated')
                    ->success()
                    ->send();
            });
    }
}
