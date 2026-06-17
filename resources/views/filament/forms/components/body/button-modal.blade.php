@props([
    'id',
])

@php
    $modalId = "{$id}-body-button-modal";
    $modalIdJs = \Illuminate\Support\Js::from($modalId);
@endphp

<x-filament::modal
    :id="$modalId"
    width="3xl"
    heading="Insert Button"
>
    <div class="space-y-4">
        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Theme</label>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="buttonTheme">
                    <option value="primary">Primary (Blue)</option>
                    <option value="secondary">Secondary (Yellow)</option>
                    <option value="light">Light Blue</option>
                    <option value="negative">Negative (Red)</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Size</label>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="buttonSize">
                    <option value="sm">Small</option>
                    <option value="md">Medium</option>
                    <option value="lg">Large</option>
                    <option value="xl">XL</option>
                    <option value="xxl">XXL</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Label</label>

            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    x-model="buttonLabel"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Button Link</label>

            <x-filament::input.wrapper>
                <x-filament::input
                    type="url"
                    x-model="buttonHref"
                />
            </x-filament::input.wrapper>
        </div>

        <label class="flex items-center gap-2">
            <x-filament::input.checkbox x-model="buttonNewWindow" />

            <span class="text-sm">Open in new tab</span>
        </label>

        <label class="flex items-center gap-2">
            <x-filament::input.checkbox x-model="buttonBoldText" />

            <span class="text-sm">Make text bold</span>
        </label>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Wrapper custom styles</label>

            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    x-model="buttonWrapperStyles"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="fi-fo-field-label text-sm font-medium">Preview</label>

            <div class="border p-2">
                <iframe
                    x-bind:src="buttonPreviewUrl()"
                    class="w-full"
                    style="height: 100px"
                ></iframe>
            </div>
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
            x-on:click="addButton()"
        >
            Insert
        </x-filament::button>
    </x-slot>
</x-filament::modal>
