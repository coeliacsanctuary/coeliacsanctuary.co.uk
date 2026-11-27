<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Filament\Forms\Components\EateryOrBranchLookup;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAlreadyExistsNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Notifications\AnonymousNotifiable;

class IgnoreAndSendAlreadyExistsAction
{
    public static function make(): Action
    {
        return Action::make('ignoreAndSendAlreadyExists')
            ->label('Ignore and send place already exists email')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('warning')
            ->visible(fn (EateryRecommendation $record): bool => ! $record->completed)
            ->schema([
                EateryOrBranchLookup::make('eatery')
                    ->required(),
            ])
            ->action(function (EateryRecommendation $record, array $data): void {
                [$eateryId, $branchId] = explode(':', $data['eatery']);

                $eatery = Eatery::query()->findOrFail($eateryId);
                $branch = null;

                if ($branchId) {
                    $branch = $eatery->nationwideBranches()->findOrFail($branchId);
                }

                new AnonymousNotifiable()
                    ->route('mail', $record->email)
                    ->notify(new EateryRecommendationAlreadyExistsNotification($record, $eatery, $branch));

                $record->update(['ignored' => true]);

                Notification::make()
                    ->title('Recommendation ignored and email sent')
                    ->success()
                    ->send();
            });
    }
}
