<?php

declare(strict_types=1);

namespace App\Filament\Tables\Recipes;

use App\Filament\Fields\Status\Table\StatusColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;

class RecipeTable
{
    /** @return Column[] */
    public static function make(): array
    {
        return [
            TextColumn::make('id')->searchable(),

            TextColumn::make('title')->searchable(),

            StatusColumn::make(),

            TextColumn::make('created_at')->dateTime(),

            TextColumn::make('publish_at')->dateTime(),
        ];
    }
}
