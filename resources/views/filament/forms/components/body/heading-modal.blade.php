@props([
    'id',
])

@php
    $modalId = "{$id}-body-heading-modal";
    $modalIdJs = \Illuminate\Support\Js::from($modalId);
@endphp

<x-filament::modal
    :id="$modalId"
    width="xl"
    heading="Insert Heading"
>
    <div class="space-y-1">
        <label class="fi-fo-field-label text-sm font-medium">Heading</label>

        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                x-model="heading"
            />
        </x-filament::input.wrapper>
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
            x-on:click="addHeader()"
        >
            Insert
        </x-filament::button>
    </x-slot>
</x-filament::modal>
