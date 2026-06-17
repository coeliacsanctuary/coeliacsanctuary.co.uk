@props([
    'id',
])

@php
    $modalId = "{$id}-body-image-insert-modal";
    $modalIdJs = \Illuminate\Support\Js::from($modalId);
@endphp

<x-filament::modal
    :id="$modalId"
    width="xl"
    heading="Insert Image"
>
    <div class="space-y-4">
        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Position</label>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="$store.bodyImageInsert.position">
                    <option value="left">Left Align</option>
                    <option value="right">Right Align</option>
                    <option value="fullwidth">Full Width</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Caption</label>

            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    x-model="$store.bodyImageInsert.title"
                />
            </x-filament::input.wrapper>
        </div>
    </div>

    <x-slot name="footer">
        <x-filament::button
            type="button"
            color="gray"
            x-on:click="$dispatch('close-modal', { id: {{ $modalIdJs }} })"
        >
            Cancel
        </x-filament::button>

        <x-filament::button
            type="button"
            x-on:click="$dispatch('body-image-do-insert')"
        >
            Insert
        </x-filament::button>
    </x-slot>
</x-filament::modal>
