<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { EateryFilters, TownEatery, LondonAreaPage } from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import { Ref, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import useScreensize from '@/composables/useScreensize';
import useInfiniteScrollCollection from '@/composables/useInfiniteScrollCollection';
import GoogleAd from '@/Components/GoogleAd.vue';
import { RequestPayload } from '@inertiajs/core';
import useBrowser from '@/composables/useBrowser';
import Info from '@/Components/Info.vue';
import { pluralise } from '@/helpers';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';

type AlternateArea = {
  borough: string;
  link: string;
  locations: number;
};

defineProps<{
  area: LondonAreaPage;
  alternateAreas?: AlternateArea[];
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
}>();

const placeList = ref<HTMLElement | null>(null);
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
    :county="area.borough"
    :image="area.image"
    :name="area.name"
    :latlng="area.latlng"
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <p class="prose-md prose max-w-none lg:prose-lg">
      In our comprehensive eating out guide, you will find a wide range of
      gluten-free options available at various locations in the
      <span class="font-semibold">{{ area.name }}</span> area of
      <span class="font-semibold">{{ area.borough.name }}, London</span>. From
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

    <div
      ref="placeList"
      class="flex flex-col space-y-4 xmd:w-3/4 xmd:flex-1"
    >
      <Info
        v-if="alternateAreas && alternateAreas.length"
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
                {{ pluralise('location', alternateArea.locations) }}
              </Link>
            </li>
          </ul>
        </div>
      </Info>

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

  <JumpToContentButton
    v-if="placeList"
    :anchor="placeList"
    label="Jump to Eatery List"
    side="left"
  />
</template>
