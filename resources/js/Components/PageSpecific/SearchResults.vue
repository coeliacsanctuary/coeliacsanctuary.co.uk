<script setup lang="ts">
import {
  EaterySearchResult,
  SearchableItem,
  SearchResult,
} from '@/types/Search';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import RecipeSquareImage from '@/Components/PageSpecific/Recipes/RecipeSquareImage.vue';
import Loader from '@/Components/Loader.vue';
import Card from '@/Components/Card.vue';
import { Link, router } from '@inertiajs/vue3';
import { PaginatedResponse } from '@/types/GenericTypes';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import useSearch from '@/composables/useSearch';
import { pluralise } from '@/helpers';
import { onMounted, ref } from 'vue';

const props = defineProps<{
  results: PaginatedResponse<SearchResult>;
  shouldLoad: boolean;
  landmark: Element;
  hasEatery: boolean;
  location: string;
  term: string;
}>();

const emits = defineEmits(['mounted']);

onMounted(() => {
  emits('mounted');
});

const { searchForm } = useSearch();

const { reset, pause, items, refreshUrl, requestOptions } =
  useInfiniteScrollCollection<SearchResult>('results', ref(props.landmark));

const isNotEatery = (type: SearchableItem): boolean => {
  return type !== 'Eatery' && type !== 'Nationwide Branch';
};

const formatDistance = (distance: number): string => {
  const roundedDistance = distance.toFixed(2);
  const label = pluralise('mile', distance);

  return `${roundedDistance} ${label} away`;
};

const itemTypeClasses = (type: SearchableItem): string[] => {
  const base = [
    'rounded-lg',
    'px-2',
    'py-2',
    'leading-none',
    'text-xs',
    'font-semibold',
    'lg:text-base',
    'lg:px-4',
  ];

  switch (type) {
    case 'Blog':
      base.push('bg-primary');
      break;
    case 'Recipe':
      base.push('bg-primary-light');
      break;
    case 'Eatery':
    case 'Nationwide Branch':
      base.push('bg-secondary');
      break;
    case 'Shop Product':
      base.push('bg-primary-other text-white');
      break;
  }

  return base;
};

const goToEaterySearch = () => {
  router.post('/wheretoeat/search', {
    term: props.term,
    range: 5,
  });
};

defineExpose({ reset, pause, refreshUrl, requestOptions });
</script>

<template>
  <Card
    v-if="searchForm.processing || shouldLoad"
    class="mt-4! w-full"
  >
    <Loader
      color="primary"
      :display="true"
      :absolute="false"
      size="size-12"
    />
  </Card>

  <Card
    v-else-if="items.length === 0"
    class="mt-4! w-full"
  >
    <div class="px-4 py-8 text-center text-xl font-semibold text-primary-dark">
      No results found!
    </div>
  </Card>

  <div
    v-else
    class="group flex min-h-screen flex-col space-y-2 xmd:-ml-3! xmd:pt-2"
  >
    <Card
      v-if="hasEatery"
      class="mx-4 rounded-xl border-2 border-primary"
    >
      <p class="prose max-w-none lg:prose-xl">
        If you're looking for places to eat in
        <strong v-text="location" />, you can find more detailed results in our
        <a
          class="inline-block cursor-pointer font-semibold"
          @click.prevent="goToEaterySearch()"
        >
          Eating Out guide
        </a>
      </p>
    </Card>

    <Card
      v-for="item in items"
      :key="item.link"
      class="group/item transform p-4 transition-all sm:scale-95 sm:group-hover:opacity-50 sm:hover:scale-100 sm:hover:opacity-100!"
    >
      <Link
        :href="item.link"
        class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4"
        prefetch
      >
        <div class="w-full sm:max-xl:w-1/4 xl:w-1/5">
          <RecipeSquareImage
            v-if="item.type === 'Recipe'"
            :src="item.image"
            :alt="item.title"
          />
          <img
            v-else-if="isNotEatery(item.type)"
            :src="item.image"
            :alt="item.title"
          />
          <StaticMap
            v-else
            :lng="(<EaterySearchResult>item).image.lng"
            :lat="(<EaterySearchResult>item).image.lat"
            :can-expand="false"
          />
        </div>

        <div class="flex flex-col space-y-4 sm:flex-1 sm:space-y-2">
          <h2
            class="text-xl font-semibold text-primary-dark transition group-hover/item:text-black lg:max-xl:text-2xl xl:text-3xl"
            v-text="item.title"
          />

          <p
            v-if="typeof item.description === 'string'"
            class="prose max-w-none flex-1 lg:prose-xl"
            v-text="item.description"
          />

          <div
            v-for="(restaurant, index) in item.description"
            v-else
            :key="index"
          >
            <p
              class="prose-xl max-w-none flex-1 font-semibold lg:prose-2xl"
              v-text="restaurant.title"
            />
            <p
              class="prose max-w-none flex-1 lg:prose-xl"
              v-text="restaurant.info"
            />
          </div>

          <div class="mt-auto flex items-end justify-between">
            <div
              :class="itemTypeClasses(item.type)"
              v-text="item.type"
            />
            <div
              v-if="(<EaterySearchResult>item)?.distance"
              class="text-sm text-grey-off-dark group-hover/item:text-grey-dark lg:text-base"
              v-text="
                formatDistance(<number>(<EaterySearchResult>item).distance)
              "
            />
          </div>
        </div>
      </Link>
    </Card>
  </div>
</template>
