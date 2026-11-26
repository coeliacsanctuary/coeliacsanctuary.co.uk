<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Actions;

use App\Models\EatingOut\EateryMagicRouteRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RegenerateMagicRouteBodyAction
{
    public static function make(): Action
    {
        return Action::make('regenerateMagicRouteBody')
            ->label('Regenerate Magic Route Body')
            ->icon(Heroicon::Sparkles)
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (EateryMagicRouteRecord $record): void {
                $agent = $record->resolver_type->agent([$record]);

                $record->update([
                    'body' => json_decode($agent->prompt('Generate the content')->text, true),
                ]);

                Notification::make()
                    ->title('Magic route body regenerated')
                    ->success()
                    ->send();
            });
    }
}
