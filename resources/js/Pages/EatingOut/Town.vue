<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  EateryFilters,
  MagicRouteGuide,
  NearbyCounty,
  NearbyTown,
  TownEatery,
  TownPage,
} from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { ref, useTemplateRef, watch } from 'vue';
import { router, Link, InfiniteScroll } from '@inertiajs/vue3';
import useEateryFilters from '@/composables/useEateryFilters';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import useJourneyTracking from '@/composables/useJourneyTracking';
import SubHeading from '@/Components/SubHeading.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import { pluralise } from '@/helpers';

const props = defineProps<{
  live_eateries_count: number;
  town: TownPage;
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
  sort: {
    current: string;
    options: FormSelectOption[];
  };
  nearby: NearbyTown[];
  guides: MagicRouteGuide[];
}>();

const placeList = ref<HTMLElement | null>(null);

const sortOption = ref(props.sort.current);

const { handleFiltersChanged } = useEateryFilters();

const reloadEateries = () => {
  router.reload({
    only: ['eateries'],
    reset: ['eateries'],
  });
};

watch(sortOption, () => {
  router.reload({
    only: ['eateries', 'sort'],
    reset: ['eateries'],
    data: { sort: sortOption.value },
  });
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('placeList'),
  'scrolled_into_view',
  'WhereToEatTownList',
);
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
    <div
      class="prose-md prose w-full max-w-none lg:prose-lg"
      v-html="town.description"
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

  <div
    v-if="live_eateries_count > 0"
    class="content_hint"
  />

  <SidebarLayout>
    <template #sidebar>
      <Card
        v-if="guides.length > 0"
        class="flex flex-col space-y-3"
      >
        <SubHeading> Specialist guides in {{ town.name }} </SubHeading>

        <p class="prose mt-4 max-w-none">
          Are you heading to {{ town.name }}? Take a look at these specialist
          guides I've put together for eating gluten free across
          {{ town.name }}!
        </p>

        <ul>
          <li
            v-for="guide in guides"
            :key="guide.link"
          >
            <Link
              :href="guide.link"
              class="text-lg font-semibold text-primary-dark hover:text-black"
            >
              {{ guide.title }}
            </Link>
          </li>
        </ul>
      </Card>

      <Card>
        <SubHeading>Other towns nearby</SubHeading>

        <div class="mt-4 flex flex-col space-y-4">
          <div
            v-for="nearbyTown in nearby"
            :key="nearbyTown.link"
            class="flex flex-col space-y-2"
          >
            <Link
              class="text-lg font-semibold text-primary-darkest transition hover:text-black lg:text-xl"
              :href="nearbyTown.link"
            >
              {{ nearbyTown.name }}
            </Link>

            <ul class="flex space-x-4">
              <li
                v-if="nearbyTown.eateries > 0"
                class="rounded-lg bg-primary/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyTown.eateries }}
                {{ pluralise('Eatery', nearbyTown.eateries) }}
              </li>

              <li
                v-if="nearbyTown.attractions > 0"
                class="rounded-lg bg-primary-dark/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyTown.attractions }}
                {{ pluralise('Attraction', nearbyTown.attractions) }}
              </li>

              <li
                v-if="nearbyTown.hotels > 0"
                class="rounded-lg bg-secondary/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyTown.hotels }}
                {{ pluralise('Hotel', nearbyTown.hotels) }}
              </li>
            </ul>
          </div>
        </div>
      </Card>

      <TownFilterSidebar
        v-if="live_eateries_count > 0"
        :filters="filters"
        fixed
        @filters-updated="handleFiltersChanged"
        @sidebar-closed="reloadEateries"
      />
    </template>

    <div
      v-if="live_eateries_count > 0"
      ref="placeList"
      class="flex flex-col"
    >
      <Card
        class="mb-4 flex space-y-2 xs:flex-row xs:items-center xs:justify-between xs:space-y-0"
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
  </SidebarLayout>

  <JumpToContentButton
    v-if="placeList"
    :anchor="placeList"
    label="Jump to Eatery List"
    side="left"
  />
</template>
