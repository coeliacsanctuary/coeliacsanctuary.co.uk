<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { EateryFilters, TownEatery, TownPage } from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { Ref, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import useScreensize from '@/composables/useScreensize';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import GoogleAd from '@/Components/GoogleAd.vue';
import { RequestPayload } from '@inertiajs/core';
import useBrowser from '@/composables/useBrowser';

defineProps<{
  town: TownPage;
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
}>();

const landmark: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;

const { items, reset } = useInfiniteScrollCollection<TownEatery>(
  'eateries',
  landmark,
);

const { screenIsGreaterThanOrEqualTo } = useScreensize();

const handleFiltersChanged = ({
  filters,
  preserveState = true,
}: {
  filters: EateryFilters;
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

  reset();

  const params: RequestPayload & {
    filter?: { [T in 'category' | 'venueType' | 'feature']?: string };
  } = {};

  if (categoryFilter.length || venueFilter.length || featureFilter.length) {
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
  }

  const lastScroll = window.scrollY;

  router.get(useBrowser().currentPath(), params, {
    preserveState: screenIsGreaterThanOrEqualTo('xmd') ? false : preserveState,
    preserveScroll: true,
    onFinish: () => {
      // This avoids race conditions with hydration
      requestAnimationFrame(() => {
        window.scrollTo(0, lastScroll);
      });
    },
  });
};

const reloadEateries = () => {
  reset();

  router.reload({
    only: ['eateries'],
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<template>
  <TownHeading
    :county="town.county"
    :image="town.image"
    :name="town.name"
    :latlng="town.latlng"
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <p class="prose-md prose max-w-none lg:prose-lg">
      In our comprehensive eating out guide, you will find a wide range of
      gluten-free options available at various locations in
      <span class="font-semibold">{{ town.name }}, {{ town.county.name }}</span
      >. From cafes, restaurants, attractions, to hotels, we've got you covered.
    </p>

    <p class="prose-md prose max-w-none lg:prose-lg">
      The wealth of information in our guide is a result of the generous
      contributions from people like you - fellow Coeliacs or individuals with
      gluten intolerance, who are familiar with their local area. These
      kind-hearted individuals take the time to share their knowledge and help
      us build a comprehensive list of places to eat to help others, like you!
    </p>

    <Warning>
      <p>
        While we take every care to make sure our eating out guide is accurate,
        places can change without notice, we always recommend that you check
        ahead before making plans.
      </p>

      <p class="mt-2">
        All eateries are recommended by our website visitors, and before going
        live we check menus and reviews, but we do not vet or visit places to
        independently check them.
      </p>
    </Warning>
  </Card>

  <GoogleAd code="5284484376" />

  <div class="relative xmd:space-x-2 md:flex">
    <TownFilterSidebar
      :filters="filters"
      @filters-updated="handleFiltersChanged"
      @sidebar-closed="reloadEateries"
    />

    <div class="flex flex-col space-y-4 xmd:w-3/4 xmd:flex-1">
      <template v-if="items.length">
        <EateryCard
          v-for="eatery in items"
          :key="eatery.link"
          :eatery="eatery"
        />
      </template>

      <Card
        v-else
        class="px-8 py-8 text-center text-xl"
      >
        No eateries found, try updating your filters!
      </Card>
      <div ref="landmark" />
    </div>
  </div>
</template>
