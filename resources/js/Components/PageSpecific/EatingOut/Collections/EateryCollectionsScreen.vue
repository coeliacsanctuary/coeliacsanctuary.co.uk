<script setup lang="ts">
import { PaginatedCollection } from '@/types/GenericTypes';
import { EateryCollectionFilters, TownEatery } from '@/types/EateryTypes';
import { ref, useTemplateRef } from 'vue';
import useScreensize from '@/composables/useScreensize';
import { RequestPayload } from '@inertiajs/core';
import { router, InfiniteScroll } from '@inertiajs/vue3';
import useBrowser from '@/composables/useBrowser';
import useJourneyTracking from '@/composables/useJourneyTracking';
import Card from '@/Components/Card.vue';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import EateryCollectionFilterSidebar from '@/Components/PageSpecific/EatingOut/Collections/EateryCollectionFilterSidebar.vue';

defineProps<{
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryCollectionFilters;
}>();

const placeList = ref<HTMLElement | null>(null);

const { screenIsGreaterThanOrEqualTo } = useScreensize();

const handleFiltersChanged = ({
  filters,
  preserveState = true,
}: {
  filters: EateryCollectionFilters;
  preserveState: boolean;
}) => {
  const categoryFilter = filters.categories
    .filter((filter) => filter.checked)
    .map((filter) => filter.value);

  const venueFilter = filters.venueTypes
    .filter((filter) => filter.checked)
    .map((filter) => filter.value);

  const featureFilter = filters.features
    .filter((filter) => filter.checked)
    .map((filter) => filter.value);

  const townFilter = filters.towns
    .filter((filter) => filter.checked)
    .map((filter) => filter.value);

  const countyFilter = filters.counties
    .filter((filter) => filter.checked)
    .map((filter) => filter.value);

  const params: RequestPayload & {
    filter?: {
      [T in 'category' | 'venueType' | 'feature' | 'town' | 'county']?: string;
    };
  } = {};

  if (
    categoryFilter.length ||
    venueFilter.length ||
    featureFilter.length ||
    townFilter.length ||
    countyFilter.length
  ) {
    params.filter = {};

    if (categoryFilter.length) {
      params.filter.category = categoryFilter.join(',');
    }

    if (venueFilter.length) {
      params.filter.venueType = venueFilter.join(',');
    }

    if (featureFilter.length) {
      params.filter.feature = featureFilter.join(',');
    }

    if (townFilter.length) {
      params.filter.town = townFilter.join(',');
    }

    if (countyFilter.length) {
      params.filter.county = countyFilter.join(',');
    }
  }

  const lastScroll = window.scrollY;

  router.get(useBrowser().currentPath(), params, {
    preserveState: screenIsGreaterThanOrEqualTo('xmd') ? false : preserveState,
    preserveScroll: true,
    only: ['eateries'],
    reset: ['eateries'],
    onFinish: () => {
      // This avoids race conditions with hydration
      requestAnimationFrame(() => {
        window.scrollTo(0, lastScroll);
      });
    },
  });
};

const reloadEateries = () => {
  router.reload({
    only: ['eateries'],
    reset: ['eateries'],
    preserveState: true,
    preserveScroll: true,
  });
};

useJourneyTracking().logWhenVisible(
  useTemplateRef('placeList'),
  'scrolled_into_view',
  'WhereToEatEateryCollectionList',
);
</script>

<template>
  <div class="relative md:flex xmd:space-x-2">
    <EateryCollectionFilterSidebar
      :filters="filters"
      @filters-updated="handleFiltersChanged"
      @sidebar-closed="reloadEateries"
    />

    <div
      ref="placeList"
      class="flex flex-col xmd:w-3/4 xmd:flex-1"
    >
      <InfiniteScroll
        data="eateries"
        only-next
        preserve-url
        class="flex flex-col space-y-4"
      >
        <template v-if="eateries.data.length">
          <template
            v-for="(eatery, index) in eateries.data"
            :key="eatery.link"
          >
            <EateryCard :eatery="eatery" />

            <div
              v-if="index > 0 && index % 4 === 0"
              class="content_hint"
            />
          </template>
        </template>

        <Card
          v-else
          class="px-8 py-8 text-center text-xl"
        >
          No eateries found, try updating your filters!
        </Card>
      </InfiniteScroll>
    </div>
  </div>
</template>
