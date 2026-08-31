<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Models\Recipes\Recipe;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;

class RelatedRecipesSearch extends Field
{
    protected string $view = 'filament.forms.components.related-recipes-search';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->loadStateFromRelationshipsUsing(static function (RelatedRecipesSearch $component, ?Recipe $record): void {
            if ( ! $record) {
                return;
            }

            $record->loadMissing('relatedRecipes');

            $component->state($record->relatedRecipes->pluck('id')->all());
        });

        $this->saveRelationshipsUsing(static function (?Recipe $record, mixed $state): void {
            $record?->relatedRecipes()->sync(
                static::ids($state)
                    ->mapWithKeys(fn (int $id, int $position): array => [$id => ['position' => $position]])
                    ->all()
            );
        });

        $this->dehydrated(false);
    }

    /** @return array<int, array{id: int, title: string, image: string|null}> */
    public function getSelectedRecipes(): array
    {
        $ids = static::ids($this->getState());

        if ($ids->isEmpty()) {
            return [];
        }

        $recipes = Recipe::query()
            ->withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $ids
            ->map(fn (int $id) => $recipes->get($id))
            ->filter()
            ->map($this->toResult(...))
            ->values()
            ->all();
    }

    /** @return array<int, array{id: int, title: string, image: string|null}> */
    #[ExposedLivewireMethod]
    #[Renderless]
    public function searchRecipes(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        return Recipe::query()
            ->withoutGlobalScopes()
            ->whereLike('title', "%{$term}%")
            ->whereNotIn('id', $this->excludedIds())
            ->limit(10)
            ->get()
            ->map($this->toResult(...))
            ->all();
    }

    /** @return array<int, int> */
    protected function excludedIds(): array
    {
        $record = $this->getRecord();

        return static::ids($this->getState())
            ->when($record instanceof Recipe, fn (Collection $ids) => $ids->push($record->getKey()))
            ->unique()
            ->values()
            ->all();
    }

    /** @return array{id: int, title: string, image: string|null} */
    protected function toResult(Recipe $recipe): array
    {
        return [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'image' => $recipe->getFirstMediaUrl('primary') ?: ($recipe->getFirstMediaUrl('square') ?: null),
        ];
    }

    /** @return Collection<int, int> */
    protected static function ids(mixed $state): Collection
    {
        return collect(is_array($state) ? $state : [])
            ->filter(fn (mixed $id): bool => filled($id) && is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values();
    }
}
