@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $rows = $getRows();
    $placeholder = $getPlaceholder();
    $shouldAutosize = $shouldAutosize();
    $statePath = $getStatePath();
    $hasToolbar = $hasToolbar();
    $hasImages = $hasImages();
    $id = $getId();

    $initialHeight = (($rows ?? 2) * 1.5) + 0.75;

    $headingModalId = "{$id}-body-heading-modal";
    $buttonModalId = "{$id}-body-button-modal";
    $iframeModalId = "{$id}-body-iframe-modal";
    $imagesModalId = "{$id}-body-images-modal";
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    class="fi-fo-textarea-wrp"
>
    <div
        x-data="bodyField({
            state: $wire.$entangle('{{ $statePath }}'),
            shouldAutosize: @js($shouldAutosize),
            initialHeight: @js($initialHeight),
            headingModalId: @js($headingModalId),
            buttonModalId: @js($buttonModalId),
            iframeModalId: @js($iframeModalId),
            imagesModalId: @js($imagesModalId),
            previewButtonUrl: @js(url('/cs-adm/preview-button')),
        })"
        wire:ignore.self
    >
        <x-filament::input.wrapper
            :disabled="$isDisabled"
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                    ->class([
                        'fi-fo-textarea',
                        'fi-autosizable' => $shouldAutosize,
                    ])
            "
        >
            <div class="flex w-full flex-col">
                @if ($hasToolbar)
                    <x-body::toolbar :id="$id" :has-images="$hasImages" />
                @endif

                <div wire:ignore.self style="height: '{{ $initialHeight . 'rem' }}'">
                    <textarea
                        x-ref="textarea"
                        @if ($shouldAutosize)
                            x-intersect.once="resize()"
                            x-on:resize.window="resize()"
                        @endif
                        x-on:click="recordCursorPosition()"
                        x-on:keyup="recordCursorPosition()"
                        x-model="state"
                        @if ($isGrammarlyDisabled())
                            data-gramm="false"
                            data-gramm_editor="false"
                            data-enable-grammarly="false"
                        @endif
                        {{ $getExtraAlpineAttributeBag() }}
                        {{
                            $getExtraInputAttributeBag()
                                ->merge([
                                    'autocomplete' => $getAutocomplete(),
                                    'autofocus' => $isAutofocused(),
                                    'cols' => $getCols(),
                                    'disabled' => $isDisabled,
                                    'id' => $id,
                                    'maxlength' => (! $isConcealed) ? $getMaxLength() : null,
                                    'minlength' => (! $isConcealed) ? $getMinLength() : null,
                                    'placeholder' => filled($placeholder) ? e($placeholder) : null,
                                    'readonly' => $isReadOnly(),
                                    'required' => $isRequired() && (! $isConcealed),
                                    'rows' => $rows,
                                    $applyStateBindingModifiers('wire:model') => $statePath,
                                ], escape: false)
                        }}
                    ></textarea>
                </div>
            </div>
        </x-filament::input.wrapper>

        <p
            x-cloak
            x-show="customError"
            x-text="customError"
            class="fi-fo-field-wrp-error-message"
        ></p>

        @if ($hasToolbar)
            <x-body::heading-modal :id="$id" />

            <x-body::button-modal :id="$id" />

            <x-body::iframe-modal :id="$id" />
        @endif

        @if ($hasImages)
            <x-body::images-modal :id="$id" :field="$field" />

            <x-body::docked-gallery :id="$id" />
        @endif
    </div>
</x-dynamic-component>
