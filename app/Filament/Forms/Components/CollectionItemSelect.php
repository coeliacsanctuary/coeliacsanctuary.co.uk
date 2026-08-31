<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Dto\CollectionItemDto;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CollectionItemSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable();
        $this->allowHtml();
        $this->optionsLimit(10);

        $this->getSearchResultsUsing(function (Get $get, string $search): array {
            $type = $get('item_type');

            if ( ! $type) {
                return [];
            }

            return static::searchQuery($type, $search)
                ->limit(10)
                ->get()
                ->mapWithKeys(fn (Model $item): array => [
                    $item->getKey() => static::optionLabel($item, $type),
                ])
                ->all();
        });

        $this->getOptionLabelUsing(function (Get $get, mixed $value): ?string {
            $type = $get('item_type');

            if ( ! $type || blank($value)) {
                return null;
            }

            $item = static::baseQuery($type)->find($value);

            return $item ? static::optionLabel($item, $type) : null;
        });
    }

    /** @param class-string<Model> $type */
    protected static function optionLabel(Model $item, string $type): string
    {
        return view('filament.forms.components.collection-item-option', [
            'item' => CollectionItemDto::fromModel($item, $type),
        ])->render();
    }

    /**
     * @param  class-string<Model>  $type
     * @return Builder<Model>
     */
    protected static function searchQuery(string $type, string $search): Builder
    {
        return match ($type) {
            Blog::class => static::baseQuery($type)
                ->where(fn (Builder $query) => $query
                    ->whereLike('title', "%{$search}%")
                    ->orWhereLike('slug', "%{$search}%")),
            Recipe::class => static::baseQuery($type)->whereLike('title', "%{$search}%"),
            Eatery::class => static::baseQuery($type)
                ->where(fn (Builder $query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('info', "%{$search}%")),
            NationwideBranch::class => static::baseQuery($type)->whereLike('name', "%{$search}%"),
            default => throw new InvalidArgumentException("Unknown collection item type [{$type}]."),
        };
    }

    /**
     * @param  class-string<Model>  $type
     * @return Builder<Model>
     */
    protected static function baseQuery(string $type): Builder
    {
        $query = $type::query()->withoutGlobalScopes();

        return match ($type) {
            Blog::class, Recipe::class => $query->with('media'),
            NationwideBranch::class => $query->with([
                'eatery' => fn ($relation) => $relation->withoutGlobalScopes(),
            ]),
            default => $query,
        };
    }
}
