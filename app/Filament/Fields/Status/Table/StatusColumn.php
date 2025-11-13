<?php

declare(strict_types=1);

namespace App\Filament\Fields\Status\Table;

use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Filament\Tables\Columns\TextColumn;

class StatusColumn
{
    public static function make(): TextColumn
    {
        return TextColumn::make('status')
            ->badge()
            ->state(function (Blog|Recipe $record): string {
                if ($record->live) {
                    return 'Live';
                }

                if ($record->publish_at) {
                    return 'Pending';
                }

                return 'Draft';
            })
            ->color(fn (string $state): string => match ($state) {
                'Live' => 'success',
                'Draft' => 'gray',
                'Pending' => 'warning',
            });
    }
}
