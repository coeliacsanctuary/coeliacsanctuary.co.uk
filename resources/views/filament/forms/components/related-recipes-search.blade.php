@php
    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();
    $selected = $getSelectedRecipes();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath }}'),
            componentKey: @js($getKey()),
            selected: @js($selected),
            term: '',
            results: [],
            open: false,
            searching: false,
            debounce: null,

            search() {
                clearTimeout(this.debounce)

                if (this.term.trim() === '') {
                    this.results = []
                    this.open = false
                    this.searching = false

                    return
                }

                this.searching = true

                this.debounce = setTimeout(async () => {
                    this.results = await this.$wire.callSchemaComponentMethod(
                        this.componentKey,
                        'searchRecipes',
                        { term: this.term },
                    )

                    this.searching = false
                    this.open = true
                }, 300)
            },

            add(recipe) {
                if (this.selected.some((item) => item.id === recipe.id)) {
                    return
                }

                this.selected.push(recipe)
                this.sync()

                this.term = ''
                this.results = []
                this.open = false
            },

            remove(id) {
                this.selected = this.selected.filter((item) => item.id !== id)
                this.sync()
            },

            reorder(ids) {
                const order = ids.map((id) => Number(id))

                this.selected = [...this.selected].sort(
                    (a, b) => order.indexOf(a.id) - order.indexOf(b.id),
                )

                this.sync()
            },

            sync() {
                this.state = this.selected.map((item) => item.id)
            },
        }"
        x-on:click.outside="open = false"
        class="fi-fo-related-recipes"
    >
        <div class="relative">
            <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
                <x-filament::input
                    type="text"
                    autocomplete="off"
                    placeholder="Search for a recipe..."
                    x-model="term"
                    x-on:input="search()"
                    x-on:focus="if (results.length) open = true"
                />
            </x-filament::input.wrapper>

            <div
                x-show="open && results.length"
                x-cloak
                x-transition.opacity
                class="absolute inset-x-0 top-full z-10 mt-1 max-h-80 overflow-y-auto rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <template x-for="result in results" :key="result.id">
                    <button
                        type="button"
                        x-on:click="add(result)"
                        class="flex w-full items-center gap-3 px-3 py-2 text-start transition hover:bg-gray-50 dark:hover:bg-white/5"
                    >
                        <img
                            x-show="result.image"
                            :src="result.image"
                            :alt="result.title"
                            class="h-10 w-10 shrink-0 rounded object-cover"
                        />

                        <div
                            x-show="! result.image"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500"
                        >
                            <x-heroicon-o-photo class="h-5 w-5" />
                        </div>

                        <span class="text-sm text-gray-950 dark:text-white" x-text="result.title"></span>
                    </button>
                </template>
            </div>

            <p
                x-show="searching"
                x-cloak
                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
            >
                Searching...
            </p>

            <p
                x-show="! searching && open && ! results.length && term.trim() !== ''"
                x-cloak
                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
            >
                No recipes found.
            </p>
        </div>

        <div
            x-show="selected.length"
            x-cloak
            x-sortable
            x-on:end.stop="reorder($event.target.sortable.toArray())"
            class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
        >
            <template x-for="recipe in selected" :key="recipe.id">
                <div
                    x-bind:x-sortable-item="recipe.id"
                    class="group relative overflow-hidden rounded-lg bg-white ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                >
                    <img
                        x-show="recipe.image"
                        :src="recipe.image"
                        :alt="recipe.title"
                        class="aspect-square w-full object-cover"
                    />

                    <div
                        x-show="! recipe.image"
                        class="flex aspect-square w-full items-center justify-center bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500"
                    >
                        <x-heroicon-o-photo class="h-8 w-8" />
                    </div>

                    <p
                        class="line-clamp-2 p-2 text-sm font-medium text-gray-950 dark:text-white"
                        x-text="recipe.title"
                    ></p>

                    <button
                        type="button"
                        title="Remove"
                        x-on:click="remove(recipe.id)"
                        class="absolute end-1.5 top-1.5 rounded-md bg-white/90 p-1 text-danger-600 opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100 focus:opacity-100 dark:bg-gray-900/90 dark:text-danger-400"
                    >
                        <x-heroicon-o-trash class="h-4 w-4" />
                    </button>

                    <span
                        x-sortable-handle
                        title="Drag to reorder"
                        class="absolute start-1.5 top-1.5 cursor-move rounded-md bg-white/90 p-1 text-gray-500 opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100 dark:bg-gray-900/90 dark:text-gray-400"
                    >
                        <x-heroicon-o-bars-3 class="h-4 w-4" />
                    </span>
                </div>
            </template>
        </div>
    </div>
</x-dynamic-component>
