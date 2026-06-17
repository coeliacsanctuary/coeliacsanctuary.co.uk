@php
    $items = $getGalleryItems();
@endphp

<div wire:key="gallery-{{ count($items) }}" x-init="$store.bodyImages.seed(@js($items))">
    <div class="flex flex-wrap gap-3">
        <template x-for="item in $store.bodyImages.items" :key="item.key">
            <div class="group flex w-50 flex-col gap-2 rounded-lg border border-gray-200 p-2 dark:border-white/10">
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

                    <button
                        x-show="!item.isProcessing && item.isDeletable"
                        x-on:click="$store.bodyImages.confirmDelete(item)"
                        type="button"
                        class="absolute right-1.5 top-1.5 rounded-md bg-white/90 p-1 text-danger-600 opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100 dark:bg-gray-900/90 dark:text-danger-400"
                    >
                        <x-heroicon-o-trash class="h-4 w-4" />
                    </button>
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

    <div
        x-show="$store.bodyImages.pendingDelete !== null"
        x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50"
    >
        <div class="mx-4 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Delete image?</h3>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Are you sure you want to delete
                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="$store.bodyImages.pendingDelete?.title"></span>?
                This cannot be undone.
            </p>

            <div class="mt-6 flex gap-3">
                <button
                    x-on:click="$store.bodyImages.performDelete($store.bodyImages.pendingDelete)"
                    type="button"
                    class="fi-btn fi-btn-size-sm fi-color-danger fi-btn-color-danger flex-1 rounded-lg px-3 py-2 text-sm font-semibold text-danger-600 shadow-sm ring-1 ring-danger-600/10 transition duration-75 hover:bg-danger-50 dark:text-danger-400 dark:ring-danger-400/20 dark:hover:bg-danger-400/10"
                >
                    Confirm Delete
                </button>

                <button
                    x-on:click="$store.bodyImages.cancelDelete()"
                    type="button"
                    class="fi-btn fi-btn-size-sm fi-color-gray fi-btn-color-gray flex-1 rounded-lg px-3 py-2 text-sm font-semibold text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 hover:bg-gray-50 dark:text-white dark:ring-white/20 dark:hover:bg-white/5"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
