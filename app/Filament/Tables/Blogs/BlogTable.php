<?php

declare(strict_types=1);

namespace App\Filament\Tables\Blogs;

use App\Models\Blogs\Blog;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;

class BlogTable
{
    /** @return Column[] */
    public static function make(): array
    {
        return [
            TextColumn::make('id')->searchable(),

            TextColumn::make('title')->searchable(),

            TextColumn::make('status')
                ->badge()
                ->state(function (Blog $record): string {
                    if ($record->live) {
                        return 'Live';
                    }

                    if ($record->published_at) {
                        return 'Pending';
                    }

                    return 'Draft';
                })
                ->color(fn (string $state): string => match ($state) {
                    'Live' => 'success',
                    'Draft' => 'gray',
                    'Pending' => 'warning',
                }),

            TextColumn::make('created_at')->dateTime(),
        ];
    }
}
