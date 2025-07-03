<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { EateryFilters, LatLng, TownEatery } from '@/types/EateryTypes';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { Ref, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import useScreensize from '@/composables/useScreensize';
import SearchResultsHeading from '@/Components/PageSpecific/EatingOut/SearchResults/SearchResultsHeading.vue';
import useBrowser from '@/composables/useBrowser';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import { Link } from '@inertiajs/vue3';
import Info from '@/Components/Info.vue';
import { pluralise } from '@/helpers';

const props = defineProps<{
  term: string;
  range: 1 | 2 | 5 | 10 | 20;
  image: string;
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
  latlng?: LatLng;
  county?: { name: string; link: string };
}>();

const landmark: Ref<Element> = ref();

const { items, reset } = useInfiniteScrollCollection<TownEatery>(
  'eateries',
  landmark,
);

const { screenIsGreaterThanOrEqualTo } = useScreensize();

const handleFiltersChanged = ({
  filters,
  preserveState,
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

  const params: Record<string, unknown> & {
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

  router.get(useBrowser().currentPath(), params, {
    preserveState: screenIsGreaterThanOrEqualTo('xmd') ? false : preserveState,
    preserveScroll: true,
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

watch(() => props.term, reset);
</script>

<template>
  <SearchResultsHeading
    :term="term"
    :range="range"
    :image="image"
    :latlng="latlng"
  />

  <Card
    v-if="items.length"
    class="mt-3 flex flex-col space-y-4"
  >
    <p class="prose-md prose max-w-none lg:prose-lg">
      In our comprehensive eating out guide, you will find a wide range of
      gluten-free options available at various locations around the UK, from
      cafes, restaurants, attractions, to hotels, we've got you covered.
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
        live we check menus and independent reviews. All eateries listed in our
        eating guide are in no way endorsed by Coeliac Sanctuary.
      </p>
    </Warning>
  </Card>

  <LocationSearch
    :term="term"
    :range="range"
  />

  <div class="relative xmd:space-x-2 md:flex">
    <TownFilterSidebar
      :filters="filters"
      @filters-updated="handleFiltersChanged"
      @sidebar-closed="reloadEateries"
    />

    <div class="flex flex-col space-y-4 xmd:w-3/4 xmd:flex-1">
      <Info
        v-if="county"
        class="mx-4 xmd:mx-0"
      >
        <p class="prose prose-lg max-w-none">
          It looks like you're looking for places to eat in {{ county.name }},
          you can get more detailed results on the dedicated
          <Link :href="county.link">
            {{ county.name }} page in my eating out guide.
          </Link>
        </p>
      </Info>

      <template v-if="items.length">
        <Info
          no-icon
          class="mx-4 !border-0 !py-4 text-center font-semibold !shadow-none xmd:mx-0"
        >
          Found {{ eateries.total }} {{ pluralise('result', eateries.total) }}
        </Info>

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
        No eateries found, try updating your filters or your search term!
      </Card>
      <div ref="landmark" />
    </div>
  </div>
</template>
