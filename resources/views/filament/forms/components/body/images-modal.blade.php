@props([
    'id',
    'field',
])

@php
    $modalId = "{$id}-body-images-modal";
    $modalIdJs = \Illuminate\Support\Js::from($modalId);
@endphp

<x-filament::modal
    :id="$modalId"
    width="7xl"
    heading="Image Manager"
>
    <div class="fi-body-images-field relative">
        <div class="space-y-4">
            {{ $field->getChildSchema('bodyImages') }}
        </div>

        <div
            x-show="$store.bodyImageInsert.open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="absolute right-0 top-0 w-80 space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-xl dark:border-white/10 dark:bg-gray-900"
        >
            <p class="text-sm font-semibold text-gray-950 dark:text-white">Insert Image</p>

            <div class="space-y-1">
                <label class="fi-fo-field-label text-sm font-medium text-gray-700 dark:text-gray-200">Position</label>

                <x-filament::input.wrapper>
                    <x-filament::input.select
                        x-bind:value="$store.bodyImageInsert.position"
                        x-on:change="$store.bodyImageInsert.changePosition($event.target.value)"
                    >
                        <option value="left">Left Align</option>
                        <option value="right">Right Align</option>
                        <option value="fullwidth">Full Width</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="space-y-1">
                <label class="fi-fo-field-label text-sm font-medium text-gray-700 dark:text-gray-200">Width</label>

                <x-filament::input.wrapper>
                    <x-filament::input.select
                        x-model="$store.bodyImageInsert.width"
                        x-on:change="$store.bodyImageInsert.dirty = true"
                    >
                        <option value="25">25</option>
                        <option value="33">33</option>
                        <option value="50">50</option>
                        <option value="66">66</option>
                        <option value="75">75</option>
                        <option value="100">100</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="space-y-1">
                <label class="fi-fo-field-label text-sm font-medium text-gray-700 dark:text-gray-200">Caption</label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        x-model="$store.bodyImageInsert.title"
                        x-on:change="$store.bodyImageInsert.dirty = true"
                        placeholder="Optional caption…"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="flex gap-2 pt-2">
                <x-filament::button
                    type="button"
                    color="gray"
                    size="sm"
                    x-on:click="$store.bodyImageInsert.cancel()"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button
                    type="button"
                    size="sm"
                    x-on:click="$dispatch('body-image-do-insert')"
                >
                    Insert
                </x-filament::button>
            </div>
        </div>
    </div>
    <x-slot name="footer">
        <x-filament::button
            type="button"
            color="gray"
            size="sm"
            icon="heroicon-o-arrow-right-circle"
            x-on:click="$store.bodyImages.docked = true; $dispatch('close-modal', { id: {{ $modalIdJs }} })"
        >
            Dock to sidebar
        </x-filament::button>
    </x-slot>
</x-filament::modal>
