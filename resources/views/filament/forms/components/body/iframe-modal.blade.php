@props([
    'id',
])

@php
    $modalId = "{$id}-body-iframe-modal";
    $modalIdJs = \Illuminate\Support\Js::from($modalId);
@endphp

<x-filament::modal
    :id="$modalId"
    width="xl"
    heading="Insert iFrame"
>
    <div class="space-y-1">
        <label class="fi-fo-field-label text-sm font-medium">URL</label>

        <x-filament::input.wrapper>
            <x-filament::input
                type="url"
                x-model="iframeUrl"
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
            x-on:click="addIframe()"
        >
            Insert
        </x-filament::button>
    </x-slot>
</x-filament::modal>
