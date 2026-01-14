<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { EateryFilters, TownEatery, TownPage } from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { Ref, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import useScreensize from '@/composables/useScreensize';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import { RequestPayload } from '@inertiajs/core';
import useBrowser from '@/composables/useBrowser';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';

const props = defineProps<{
  live_eateries_count: number;
  town: TownPage;
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
  sort: {
    current: string;
    options: FormSelectOption[];
  };
}>();

const placeList = ref<HTMLElement | null>(null);
const landmark: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;

const sortOption = ref(props.sort.current);

const { items, reset, requestOptions } =
  useInfiniteScrollCollection<TownEatery>('eateries', landmark);

requestOptions.value = { data: { sort: sortOption.value } };

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

watch(sortOption, () => {
  requestOptions.value = { data: { sort: sortOption.value } };

  router.reload({
    only: ['eateries', 'sort'],
    data: { sort: sortOption.value },
    onSuccess: () => reset(),
  });
});
</script>

<template>
  <TownHeading
    :county="town.county"
    :image="town.image"
    :name="town.name"
    :latlng="town.latlng"
  />

  <Card
    v-if="live_eateries_count > 0"
    class="mt-3 flex flex-col space-y-4"
  >
    <p class="prose-md prose max-w-none lg:prose-lg">
      Looking for gluten free in {{ town.name }}? In our comprehensive eating
      out guide, you will find a wide range of gluten free options available at
      various locations in
      <span
        class="font-semibold"
        v-text="town.name"
      />. From cafes, restaurants, attractions, to hotels, we've got you
      covered.
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

  <div
    v-if="live_eateries_count > 0"
    class="content_hint"
  />

  <div class="relative md:flex xmd:space-x-2">
    <TownFilterSidebar
      v-if="live_eateries_count > 0"
      :filters="filters"
      @filters-updated="handleFiltersChanged"
      @sidebar-closed="reloadEateries"
    />

    <div
      v-if="live_eateries_count > 0"
      ref="placeList"
      class="flex flex-col space-y-4 xmd:w-3/4 xmd:flex-1"
    >
      <Card
        class="flex space-y-2 xs:flex-row xs:items-center xs:justify-between xs:space-y-0"
      >
        <div class="font-semibold sm:text-lg">
          Showing eateries in {{ sort.current }} order
        </div>

        <FormSelect
          v-model="sortOption"
          name="sort"
          :options="sort.options"
          label="Sort by"
          borders
          class="flex items-center space-x-2 xs:flex-col xs:items-start xs:space-x-0 sm:flex-row sm:items-center sm:space-x-2"
          size="small"
        />
      </Card>

      <template v-if="items.length">
        <template
          v-for="(eatery, index) in items"
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
      <div ref="landmark" />
    </div>

    <Card
      v-else
      class="flex w-full flex-col space-y-4 px-8 py-8 text-center"
    >
      <p class="prose prose-xl max-w-none">
        Sorry, we don't have any places listed in {{ town.name }}.
      </p>

      <p class="prose prose-xl max-w-none">
        <Link :href="town.county.link">Back to {{ town.county.name }}</Link>
      </p>
    </Card>
  </div>

  <JumpToContentButton
    v-if="placeList"
    :anchor="placeList"
    label="Jump to Eatery List"
    side="left"
  />
</template>
