@php
    $items = $getGalleryItems();
@endphp

<div x-init="$store.bodyImages.seed(@js($items))">
    <div class="flex flex-wrap gap-3">
        <template x-for="item in $store.bodyImages.items" :key="item.key">
            <div class="flex w-50 flex-col gap-2 rounded-lg border border-gray-200 p-2 dark:border-white/10">
                <div class="relative aspect-[1200/630] overflow-hidden rounded-md bg-gray-100 dark:bg-white/5">
                    <img
                        x-show="item.src"
                        :src="item.src"
                        :class="item.isProcessing ? 'opacity-40' : 'opacity-100'"
                        class="h-full w-full object-contain transition-opacity duration-300"
                        alt=""
                    />

                    <div x-show="item.isProcessing" class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-8 w-8 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
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
                    class="fi-btn fi-btn-size-sm fi-color-gray fi-btn-color-gray w-full rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 hover:bg-gray-50 dark:text-white dark:ring-white/20 dark:hover:bg-white/5"
                >
                    Insert
                </button>
            </div>
        </template>
    </div>
</div>
