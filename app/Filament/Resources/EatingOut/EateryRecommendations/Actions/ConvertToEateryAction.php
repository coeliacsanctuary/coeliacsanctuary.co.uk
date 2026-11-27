<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Models\EatingOut\EateryRecommendation;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ConvertToEateryAction
{
    public static function make(): Action
    {
        return Action::make('convertToEatery')
            ->label('Convert to Eatery')
            ->icon(Heroicon::OutlinedPlusCircle)
            ->color('primary')
            ->visible(fn (EateryRecommendation $record): bool => ! $record->completed && ! $record->ignored)
            ->url(fn (EateryRecommendation $record): string => EateryResource::getUrl('create', ['recommendation' => $record->id]), shouldOpenInNewTab: true);
    }
}
