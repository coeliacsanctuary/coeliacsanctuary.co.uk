@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Js;
@endphp

@props([
    'id',
    'hasImages' => false,
])

@php
    $openModal = fn (string $modalId): string => "recordCursorPosition(); \$dispatch('open-modal', { id: ".Js::from($modalId)." })";

    $buttons = [
        [
            'icon' => Heroicon::H1,
            'label' => 'Add header',
            'click' => $openModal("{$id}-body-heading-modal"),
        ],
        [
            'icon' => Heroicon::Link,
            'label' => 'Add button',
            'click' => $openModal("{$id}-body-button-modal"),
        ],
        [
            'icon' => 'heroicon-o-window',
            'label' => 'Add iframe',
            'click' => $openModal("{$id}-body-iframe-modal"),
        ],
    ];

    if ($hasImages) {
        $buttons[] = [
            'icon' => Heroicon::OutlinedPhoto,
            'label' => 'Insert image',
            'click' => $openModal("{$id}-body-images-modal"),
        ];
    }
@endphp

<div class="flex items-center gap-1 border-b border-gray-200 bg-gray-50 p-1 dark:border-white/10 dark:bg-white/5">
    @foreach ($buttons as $button)
        <x-filament::icon-button
            type="button"
            color="gray"
            size="sm"
            :icon="$button['icon']"
            :icon-size="IconSize::Small"
            :label="$button['label']"
            :tooltip="$button['label']"
            class="m-0 rounded-md !p-0 border size-7 border-gray-200 bg-white dark:border-white/10 dark:bg-white/5"
            x-on:click="{{ $button['click'] }}"
        />
    @endforeach
</div>
