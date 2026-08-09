<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  EateryFilters,
  TownEatery,
  LondonAreaPage,
  NearbyTown,
  MagicRouteGuide,
} from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { ref, useTemplateRef, watch } from 'vue';
import { router, Link, InfiniteScroll } from '@inertiajs/vue3';
import useEateryFilters from '@/composables/useEateryFilters';
import Info from '@/Components/Info.vue';
import { pluralise } from '@/helpers';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import SubHeading from '@/Components/SubHeading.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';

type AlternateArea = {
  borough: string;
  link: string;
  locations: number;
};

const props = defineProps<{
  live_eateries_count: number;
  area: LondonAreaPage;
  alternateAreas?: AlternateArea[];
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
  'WhereToEatAreaList',
);
</script>

<template>
  <TownHeading
    :county="area.borough"
    :image="area.image"
    :name="area.name"
    :latlng="area.latlng"
  />

  <Card
    v-if="live_eateries_count > 0"
    class="mt-3 flex flex-col space-y-4"
  >
    <div
      class="prose-md prose w-full max-w-none lg:prose-lg"
      v-html="area.description"
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
        <SubHeading> Specialist guides in {{ area.name }} </SubHeading>

        <p class="prose mt-4 max-w-none">
          Are you heading to {{ area.name }}? Take a look at these specialist
          guides I've put together for eating gluten free across
          {{ area.name }}!
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
        <SubHeading>Other areas nearby</SubHeading>

        <div class="mt-4 flex flex-col space-y-4">
          <div
            v-for="nearbyArea in nearby"
            :key="nearbyArea.link"
            class="flex flex-col space-y-2"
          >
            <Link
              class="text-lg font-semibold text-primary-darkest transition hover:text-black lg:text-xl"
              :href="nearbyArea.link"
            >
              {{ nearbyArea.name }}
            </Link>

            <ul class="flex space-x-4">
              <li
                v-if="nearbyArea.eateries > 0"
                class="rounded-lg bg-primary/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyArea.eateries }}
                {{ pluralise('Eatery', nearbyArea.eateries) }}
              </li>

              <li
                v-if="nearbyArea.attractions > 0"
                class="rounded-lg bg-primary-dark/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyArea.attractions }}
                {{ pluralise('Attraction', nearbyArea.attractions) }}
              </li>

              <li
                v-if="nearbyArea.hotels > 0"
                class="rounded-lg bg-secondary/50 px-4 py-1 text-xs font-semibold"
              >
                {{ nearbyArea.hotels }}
                {{ pluralise('Hotel', nearbyArea.hotels) }}
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
      />
    </template>

    <div
      v-if="live_eateries_count > 0"
      ref="placeList"
      class="flex flex-col"
    >
      <Info
        v-if="alternateAreas && alternateAreas.length"
        class="mb-4"
        flex
      >
        <div>
          We have more places in
          <span
            class="font-semibold"
            v-text="area.name"
          />
          in other boroughs in our eating out guide.

          <ul class="mt-4">
            <li
              v-for="alternateArea in alternateAreas"
              :key="alternateArea.link"
              class="font-semibold text-primary-dark transition hover:text-black"
            >
              <Link :href="alternateArea.link">
                {{ alternateArea.borough }} - {{ alternateArea.locations }}
                {{ pluralise('eatery', alternateArea.locations) }}
              </Link>
            </li>
          </ul>
        </div>
      </Info>

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
        Sorry, we don't have any places listed in {{ area.name }}.
      </p>

      <p class="prose prose-xl max-w-none">
        <Link :href="area.borough.link">Back to {{ area.borough.name }}</Link>
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
