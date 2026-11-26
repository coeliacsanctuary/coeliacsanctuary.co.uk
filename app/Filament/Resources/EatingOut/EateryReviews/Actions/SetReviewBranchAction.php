<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews\Actions;

use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class SetReviewBranchAction
{
    public static function make(): Action
    {
        return Action::make('setBranch')
            ->label('Set Branch')
            ->icon(Heroicon::OutlinedMapPin)
            ->color('gray')
            ->visible(fn (EateryReview $record): bool => $record->eatery?->county_id === 1)
            ->fillForm(fn (EateryReview $record): array => ['nationwide_branch_id' => $record->nationwide_branch_id])
            ->schema([
                TextEntry::make('branch_name')
                    ->label('Branch entered by the reviewer')
                    ->state(fn (EateryReview $record): string => $record->branch_name ?: '-'),

                Select::make('nationwide_branch_id')
                    ->label('Branch')
                    ->searchable()
                    ->options(fn (EateryReview $record): array => self::branchOptions($record)),
            ])
            ->action(function (EateryReview $record, array $data): void {
                $record->update(['nationwide_branch_id' => $data['nationwide_branch_id']]);

                Notification::make()
                    ->title('Branch updated')
                    ->success()
                    ->send();
            });
    }

    /** @return array<int, string> */
    protected static function branchOptions(EateryReview $record): array
    {
        if ( ! $record->eatery) {
            return [];
        }

        return $record->eatery
            ->nationwideBranches()
            ->with(['area', 'town', 'county', 'country'])
            ->chaperone('eatery')
            ->get()
            ->mapWithKeys(fn (NationwideBranch $branch): array => [$branch->id => $branch->full_name])
            ->sort()
            ->all();
    }
}
