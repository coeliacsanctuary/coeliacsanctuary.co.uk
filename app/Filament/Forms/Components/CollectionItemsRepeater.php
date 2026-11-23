<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Dto\CollectionItemDto;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CollectionItemsRepeater extends Repeater
{
    protected string $view = 'filament.forms.components.collection-items-repeater';

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(0);

        $this->schema([
            Hidden::make('item_type'),
            Hidden::make('item_id'),
            Hidden::make('item_title'),
            Hidden::make('item_description'),
        ]);

        $this->addActionLabel('Add Item');

        $this->addAction(fn (Action $action): Action => $action
            ->modalHeading('Add Item')
            ->modalSubmitActionLabel('Save')
            ->modalWidth(Width::TwoExtraLarge)
            ->schema($this->itemFields())
            ->action(function (array $data, CollectionItemsRepeater $component): void {
                $items = $component->getRawState() ?? [];
                $key = $component->generateUuid();

                if ($key) {
                    $items[$key] = $data;
                } else {
                    $items[] = $data;
                }

                $component->rawState($items);
                $component->callAfterStateUpdated();
            }));

        $this->deleteAction(fn (Action $action): Action => $action
            ->label('Remove')
            ->modalHeading('Remove item')
            ->requiresConfirmation()
            ->link());

        $this->registerActions([
            fn (CollectionItemsRepeater $component): Action => $component->getEditAction(),
        ]);
    }

    public function getEditAction(): Action
    {
        return Action::make('edit')
            ->label('Edit')
            ->modalHeading('Edit Item')
            ->modalSubmitActionLabel('Save')
            ->modalWidth(Width::TwoExtraLarge)
            ->fillForm(fn (array $arguments, CollectionItemsRepeater $component): array => $component->getRawState()[$arguments['item']] ?? [])
            ->schema($this->itemFields())
            ->action(function (array $arguments, array $data, CollectionItemsRepeater $component): void {
                $items = $component->getRawState() ?? [];

                if ( ! array_key_exists($arguments['item'], $items)) {
                    return;
                }

                $items[$arguments['item']] = [...$items[$arguments['item']], ...$data];

                $component->rawState($items);
                $component->callAfterStateUpdated();
            })
            ->link()
            ->visible(fn (CollectionItemsRepeater $component): bool => ! $component->isDisabled());
    }

    /** @return array<int, array{key: string, title: string, meta: string|null, imageUrl: string|null, icon: Heroicon}> */
    public function getTiles(): array
    {
        $rows = collect($this->getRawState() ?? []);

        $models = $rows
            ->filter(fn (array $row): bool => filled($row['item_type'] ?? null) && filled($row['item_id'] ?? null))
            ->groupBy('item_type')
            ->map(fn (Collection $rows, string $type): Collection => CollectionItemSelect::baseQuery($type)
                ->whereKey($rows->pluck('item_id')->unique()->all())
                ->get()
                ->keyBy(fn (Model $item): mixed => $item->getKey()));

        return $rows
            ->map(function (array $row, string|int $key) use ($models): array {
                $type = $row['item_type'] ?? null;
                $model = $models->get($type)?->get($row['item_id'] ?? null);
                $item = $model ? CollectionItemDto::fromModel($model, $type) : null;

                return [
                    'key' => (string) $key,
                    'title' => ($row['item_title'] ?? null) ?: $item->title ?? 'No item selected',
                    'meta' => ($row['item_description'] ?? null) ?: ($item?->address ?: $item?->description),
                    'imageUrl' => $item?->imageUrl,
                    'icon' => match ($type) {
                        Blog::class => Heroicon::OutlinedBookOpen,
                        Recipe::class => Heroicon::OutlinedCake,
                        Eatery::class, NationwideBranch::class => Heroicon::OutlinedBuildingStorefront,
                        default => Heroicon::OutlinedPhoto,
                    },
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<int, Field> */
    protected function itemFields(): array
    {
        return [
            Select::make('item_type')
                ->label('Type')
                ->options([
                    Blog::class => 'Blog',
                    Recipe::class => 'Recipe',
                    Eatery::class => 'Eatery',
                    NationwideBranch::class => 'Nationwide Branch',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set): mixed => $set('item_id', null)),

            CollectionItemSelect::make('item_id')
                ->label('Item')
                ->required()
                ->live()
                ->visible(fn (Get $get): bool => filled($get('item_type'))),

            TextInput::make('item_title')
                ->label('Title override')
                ->helperText("Leave blank to use the item's own title")
                ->maxLength(255)
                ->visible(fn (Get $get): bool => filled($get('item_id'))),

            Textarea::make('item_description')
                ->label('Description override')
                ->helperText("Leave blank to use the item's own description")
                ->rows(4)
                ->visible(fn (Get $get): bool => filled($get('item_id'))),
        ];
    }
}
