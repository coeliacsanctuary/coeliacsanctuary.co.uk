<script setup lang="ts">
import FormLookup from '@/Components/Forms/FormLookup.vue';
import { TravelCardLookupResult } from '@/types/Shop';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  searchTerm: string | null;
}>();

const lookup = ref<null | {
  reset: () => void;
  setValue: (value: string) => void;
}>(null);

const searching = defineModel<boolean>('searching', { default: false });

const select = (term: string) => {
  lookup.value?.setValue(term);

  searching.value = true;

  router.get(
    '/gluten-free-travel-translation-cards',
    { term },
    {
      only: ['search', 'searchTerm', 'meta'],
      preserveState: true,
      preserveScroll: true,
      onFinish: () => (searching.value = false),
    },
  );
};
</script>

<template>
  <FormLookup
    ref="lookup"
    label="Search for a country or language"
    name="travel-card-search"
    placeholder="Try Spain, Greek, or Spain and France"
    size="large"
    hide-label
    borders
    class="w-full"
    lookup-endpoint="/api/shop/travel-card-search"
    :initial-value="props.searchTerm ?? ''"
    input-classes="text-lg md:text-2xl! p-2! md:p-4! text-center"
    results-classes="bg-white"
  >
    <template #item="{ term, value, type }: TravelCardLookupResult">
      <div
        class="flex cursor-pointer space-x-2 border-b border-grey-off bg-grey-light text-left transition hover:bg-grey-lightest"
        @click="select(value)"
      >
        <span
          class="flex-1 p-2"
          v-html="term"
        />

        <span
          class="flex w-[77px] shrink-0 items-center justify-center bg-grey-off-light text-center text-xs font-semibold text-grey-dark sm:w-[100px]"
        >
          {{ type.charAt(0).toUpperCase() + type.slice(1) }}
        </span>
      </div>
    </template>

    <template #no-results>
      <div class="flex flex-col space-y-2 p-3 text-center">
        <div>Sorry, nothing found</div>

        <div>
          Make sure you're searching for a country or a language, and not a city
          or place name, so search <strong>France</strong>, not
          <strong>Paris</strong> for example!
        </div>
      </div>
    </template>
  </FormLookup>
</template>
