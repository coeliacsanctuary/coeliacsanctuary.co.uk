@props([
    'id',
])

@php
    $imagesModalId = "{$id}-body-images-modal";
    $imagesModalIdJs = \Illuminate\Support\Js::from($imagesModalId);
@endphp

<div
    x-show="$store.bodyImages.docked"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed right-0 top-0 z-[100] flex h-screen w-80 flex-col border-l border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900"
>
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
        <span class="text-sm font-semibold text-gray-950 dark:text-white">Image Gallery</span>

        <div class="flex items-center gap-1">
            <button
                type="button"
                title="Open Image Manager"
                x-on:click="$store.bodyImages.docked = false; $dispatch('open-modal', { id: {{ $imagesModalIdJs }} })"
                class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                </svg>
            </button>

            <button
                type="button"
                title="Close"
                x-on:click="$store.bodyImages.docked = false"
                class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-300"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="!$store.bodyImageInsert.open" class="flex-1 overflow-y-auto p-4">
        <div class="grid grid-cols-2 gap-2">
            <template x-for="item in $store.bodyImages.items" :key="item.key">
                <div class="flex flex-col gap-1.5 rounded-lg border border-gray-200 p-1.5 dark:border-white/10">
                    <div class="relative aspect-[1200/630] overflow-hidden rounded-md bg-gray-100 dark:bg-white/5">
                        <img
                            x-show="item.src"
                            :src="item.src"
                            :class="item.isProcessing ? 'opacity-40' : 'opacity-100'"
                            class="h-full w-full object-contain transition-opacity duration-300"
                            alt=""
                        />

                        <div x-show="item.isProcessing" class="absolute inset-0 flex items-center justify-center">
                            <svg class="h-5 w-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647Z" />
                            </svg>
                        </div>
                    </div>

                    <p class="truncate text-xs text-gray-500 dark:text-gray-400" x-text="item.title"></p>

                    <button
                        x-show="!item.isProcessing"
                        x-on:click="$dispatch('body-image-open-insert', { src: item.insertSrc, title: '' })"
                        type="button"
                        class="w-full rounded px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:text-gray-200 dark:ring-white/20 dark:hover:bg-white/5"
                    >
                        Insert
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div x-show="$store.bodyImageInsert.open" class="flex-1 space-y-4 overflow-y-auto p-4">
        <p class="text-sm font-semibold text-gray-950 dark:text-white">Insert Image</p>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Position</label>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="$store.bodyImageInsert.position">
                    <option value="left">Left Align</option>
                    <option value="right">Right Align</option>
                    <option value="fullwidth">Full Width</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Caption</label>

            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    x-model="$store.bodyImageInsert.title"
                    placeholder="Optional caption…"
                />
            </x-filament::input.wrapper>
        </div>

        <div class="flex gap-2 pt-2">
            <x-filament::button
                type="button"
                color="gray"
                size="sm"
                x-on:click="$store.bodyImageInsert.open = false"
            >
                Back
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
