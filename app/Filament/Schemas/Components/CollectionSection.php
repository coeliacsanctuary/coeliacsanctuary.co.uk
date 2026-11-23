<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\Models\Collections\Collection;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class CollectionSection
{
    public static function make(string $resource): Section
    {
        return Section::make('Collection')
            ->visibleOn('create')
            ->schema([
                Select::make('add_to_collection')
                    ->label('Add to Collection')
                    ->options(fn (): array => Collection::query()->orderBy('title')->pluck('title', 'id')->all())
                    ->searchable()
                    ->nullable()
                    ->dehydrated(false)
                    ->helperText("Optional, adds this {$resource} to the end of the chosen collection, it will appear there once published.")
                    ->saveRelationshipsUsing(function ($record, mixed $state): void {
                        if (blank($state)) {
                            return;
                        }

                        $group = Collection::query()->whereKey($state)->first()?->groups->last();

                        if ( ! $group) {
                            return;
                        }

                        $record->associatedCollectionGroups()->create(['collection_group_id' => $group->id]);
                    }),
            ]);
    }
}
