@php
    $fieldWrapperView = $getFieldWrapperView();
    $tiles = $getTiles();
    $isReorderable = $isReorderableWithDragAndDrop();
    $editAction = $getAction('edit');
    $deleteAction = $getAction($getDeleteActionName());
    $addAction = $getAction($getAddActionName());
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div class="flex flex-col gap-4">
        @if ($tiles)
            <div
                x-data
                x-sortable
                x-on:end.stop="
                    $wire.mountAction(
                        @js($getReorderActionName()),
                        { items: $event.target.sortable.toArray() },
                        { schemaComponent: @js($getKey()) },
                    )
                "
                class="grid grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-3"
            >
                @foreach ($tiles as $tile)
                    <div
                        wire:key="{{ $getKey() }}.{{ $tile['key'] }}.tile"
                        x-sortable-item="{{ $tile['key'] }}"
                        class="flex flex-col overflow-hidden rounded-lg bg-white ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                    >
                        <div @if ($isReorderable) x-sortable-handle @endif @class(['flex-1', 'cursor-grab' => $isReorderable])>
                            <div
                                class="flex aspect-[16/10] items-center justify-center overflow-hidden bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500"
                            >
                                @if ($tile['imageUrl'])
                                    <img
                                        src="{{ $tile['imageUrl'] }}"
                                        alt="{{ $tile['title'] }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover"
                                    />
                                @else
                                    <x-filament::icon :icon="$tile['icon']" class="h-8 w-8" />
                                @endif
                            </div>

                            <div class="flex flex-col gap-1 p-2">
                                <p class="line-clamp-2 min-h-10 text-sm font-semibold text-gray-950 dark:text-white">{{ $tile['title'] }}</p>

                                <p class="min-h-4 truncate text-xs text-gray-500 dark:text-gray-400">{{ $tile['meta'] }}</p>
                            </div>
                        </div>

                        <div class="flex justify-between border-t border-gray-100 px-2 py-1.5 dark:border-white/10">
                            {{ $editAction(['item' => $tile['key']]) }}

                            {{ $deleteAction(['item' => $tile['key']]) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 italic dark:text-gray-500">No items in this group yet.</p>
        @endif

        <div>{{ $addAction }}</div>
    </div>
</x-dynamic-component>
