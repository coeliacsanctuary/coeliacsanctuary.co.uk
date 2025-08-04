<script setup lang="ts">
import { SearchResult as SearchResultType } from '@/types/Search';
import Loader from '@/Components/Loader.vue';
import Card from '@/Components/Card.vue';
import { PaginatedResponse } from '@/types/GenericTypes';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import useSearch from '@/composables/useSearch';
import { onMounted, ref } from 'vue';
import { LatLng } from '@/types/EateryTypes';
import SearchEateriesCta from '@/Components/PageSpecific/Search/SearchEateriesCta.vue';
import { router } from '@inertiajs/vue3';
import SearchResult from '@/Components/PageSpecific/Search/SearchResult.vue';

const props = defineProps<{
  results: PaginatedResponse<SearchResultType>;
  shouldLoad: boolean;
  landmark: Element;
  hasEatery: boolean;
  location?: string;
  searchLatLng?: LatLng;
  term: string;
}>();

const emits = defineEmits(['mounted']);

onMounted(() => {
  emits('mounted');
});

const { searchForm } = useSearch();

const { reset, pause, items, refreshUrl, requestOptions } =
  useInfiniteScrollCollection<SearchResultType>('results', ref(props.landmark));

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
    <SearchEateriesCta
      v-if="hasEatery && location"
      :location="location"
      :latlng="searchLatLng"
      @eatery-search="goToEaterySearch()"
    />

    <SearchResult
      v-for="item in items"
      :key="item.link"
      :item="item"
    />
  </div>
</template>
