<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews\Actions;

use App\Models\EatingOut\EateryReview;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Notifications\AnonymousNotifiable;

class ApproveReviewAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (EateryReview $record): bool => ! $record->approved)
            ->action(function (EateryReview $record): void {
                $record->update(['approved' => true]);

                $record->eatery?->sealiacOverview?->update(['invalidated' => true]);

                $record->eatery?->touch();

                new AnonymousNotifiable()
                    ->route('mail', $record->email)
                    ->notify(new EateryReviewApprovedNotification($record));

                Notification::make()
                    ->title('Review approved')
                    ->success()
                    ->send();
            });
    }
}
