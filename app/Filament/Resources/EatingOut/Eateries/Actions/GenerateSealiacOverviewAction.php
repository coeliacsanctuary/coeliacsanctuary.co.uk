<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Actions;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
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
            ->visible(fn (Eatery|NationwideBranch $record): bool => $record->live === true
                && ($record instanceof NationwideBranch || $record->closed_down === false)
                && $record->reviews_count > 0)
            ->action(function (Eatery|NationwideBranch $record): void {
                $eatery = $record instanceof NationwideBranch ? $record->eatery : $record;
                $branch = $record instanceof NationwideBranch ? $record : null;

                $eatery->sealiacOverview?->update(['invalidated' => true]);
                $branch?->sealiacOverview?->update(['invalidated' => true]);

                dispatch(fn () => app(GetSealiacEateryOverviewAction::class)->handle($eatery, $branch));

                Notification::make()
                    ->title('Sealiac overviews are queued to be generated')
                    ->success()
                    ->send();
            });
    }
}
