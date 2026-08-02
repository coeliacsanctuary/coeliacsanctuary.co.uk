<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  EateryFilters,
  NationwidePage,
} from '@/types/EateryTypes';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import NationwideEateryCard from '@/Components/PageSpecific/EatingOut/NationwideEateryCard.vue';
import Heading from '@/Components/Heading.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import Warning from '@/Components/Warning.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import useScreensize from '@/composables/useScreensize';
import useBrowser from '@/composables/useBrowser';
import { router } from '@inertiajs/vue3';
import { RequestPayload } from '@inertiajs/core';
import { computed, ref } from 'vue';

const props = defineProps<{
  county: NationwidePage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
  filters: EateryFilters;
}>();

const { screenIsGreaterThanOrEqualTo } = useScreensize();

const chainList = ref<HTMLElement | null>(null);
const chainSearch = ref('');

const sortOptions = ref<FormSelectOption[]>([
  { label: 'Alphabetically', value: 'alphabetical' },
  { label: 'Highest Rated', value: 'rating' },
  { label: 'Most Reviewed', value: 'reviews' },
]);

const currentSort = ref('alphabetical');

const filteredChains = computed(() => {
  const chains = props.county.chains.filter((chain) =>
    chain.name.toLowerCase().includes(chainSearch.value.toLowerCase()),
  );

  if (currentSort.value === 'rating') {
    return [...chains].sort(
      (a, b) => Number(b.reviews.average) - Number(a.reviews.average),
    );
  }

  if (currentSort.value === 'reviews') {
    return [...chains].sort((a, b) => b.reviews.number - a.reviews.number);
  }

  return chains;
});

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

const reloadChains = () => {
  router.reload({ only: ['county'] });
};
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Gluten Free Nationwide Chains in the UK </Heading>

    <div
      class="prose prose-lg max-w-none"
      v-html="county.description"
    />

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

  <SidebarLayout>
    <template #sidebar>
      <TopPlaces
        v-if="topRated.length"
        :collapsible="false"
      >
        <template #title>Top Rated Gluten Free Chain Restaurants</template>

        <template #default>
          <div class="group grid gap-3">
            <CountyEatery
              v-for="eatery in topRated"
              :key="eatery.name"
              :eatery="eatery"
              minimal
            />
          </div>
        </template>
      </TopPlaces>

      <TopPlaces
        v-if="mostRated.length"
        :collapsible="false"
      >
        <template #title>
          Most Reviewed Gluten Free Chain Restaurants
        </template>

        <template #default>
          <div class="group grid gap-3">
            <CountyEatery
              v-for="eatery in mostRated"
              :key="eatery.name"
              :eatery="eatery"
              minimal
            />
          </div>
        </template>
      </TopPlaces>

      <TownFilterSidebar
        :filters="filters"
        fixed
        @filters-updated="handleFiltersChanged"
        @sidebar-closed="reloadChains"
      />

      <div class="content_hint"></div>
    </template>

    <Card class="flex flex-col space-y-4">
      <Heading>List of Gluten Free Nationwide Chains</Heading>

      <div
        class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:space-x-4"
      >
        <FormInput
          v-model="chainSearch"
          name="search"
          label=""
          placeholder="Search for a chain..."
          hide-label
          borders
          class="w-full max-w-sm md:max-w-md"
          :size="
            useScreensize().screenIsGreaterThan('md') ? 'large' : 'default'
          "
        />

        <FormSelect
          v-model="currentSort"
          name="sort"
          :options="sortOptions"
          label="Sort by"
          borders
          class="flex items-center justify-between space-x-2 xs:flex-col xs:items-start xs:space-x-0 sm:flex-row sm:items-center sm:space-x-2"
          wrapper-classes="flex-1 sm:flex-shrink-0"
          :size="
            useScreensize().screenIsGreaterThan('md') ? 'large' : 'default'
          "
        />
      </div>
    </Card>

    <div
      ref="chainList"
      class="mt-3 flex flex-col space-y-4"
    >
      <template v-if="filteredChains.length">
        <template
          v-for="(eatery, index) in filteredChains"
          :key="eatery.key"
        >
          <NationwideEateryCard :eatery="eatery" />

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
        No chains found, try updating your search or filters!
      </Card>
    </div>
  </SidebarLayout>

  <JumpToContentButton
    v-if="chainList"
    :anchor="chainList"
    label="Jump to Chain List"
    side="left"
  />
</template>
