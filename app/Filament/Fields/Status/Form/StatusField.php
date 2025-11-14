<?php

declare(strict_types=1);

namespace App\Filament\Fields\Status\Form;

use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Recipes\Recipe;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;

class StatusField
{
    public static function make(): Select
    {
        return Select::make('status')
            ->options([
                'draft' => 'Draft',
                'live' => 'Live',
                'scheduled' => 'Scheduled',
            ])
            ->formatStateUsing(function (Blog|Recipe|Collection|null $record): string {
                if ($record?->live) {
                    return 'live';
                }

                if ($record?->publish_at) {
                    return 'scheduled';
                }

                return 'draft';
            })
            ->live()
            ->afterStateUpdated(function (Set $set, ?string $state): void {
                if ($state === 'live') {
                    $set('publish_at', null);
                }
            })
            ->dehydrateStateUsing(fn (string $state): string => ucwords($state))
            ->default('draft');
    }
}
