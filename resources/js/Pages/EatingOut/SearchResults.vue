<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { EateryFilters, LatLng, TownEatery } from '@/types/EateryTypes';
import Warning from '@/Components/Warning.vue';
import { PaginatedCollection } from '@/types/GenericTypes';
import EateryCard from '@/Components/PageSpecific/EatingOut/EateryCard.vue';
import TownFilterSidebar from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebar.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { ref, watch } from 'vue';
import { router, Link, InfiniteScroll } from '@inertiajs/vue3';
import SearchResultsHeading from '@/Components/PageSpecific/EatingOut/SearchResults/SearchResultsHeading.vue';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import Info from '@/Components/Info.vue';
import { pluralise } from '@/helpers';
import { FormSelectOption } from '@/Components/Forms/Props';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import useEateryFilters from '@/composables/useEateryFilters';

const props = defineProps<{
  term: string;
  range: 1 | 2 | 5 | 10 | 20;
  image: string;
  eateries: PaginatedCollection<TownEatery>;
  filters: EateryFilters;
  latlng?: LatLng;
  county?: { name: string; link: string };
  sort: {
    current: string;
    options: FormSelectOption[];
  };
}>();

const placeList = ref<HTMLElement | null>(null);

const sortOption = ref(props.sort.current);

const { handleFiltersChanged } = useEateryFilters();

const reloadEateries = () => {
  router.reload({
    only: ['eateries'],
    reset: ['eateries'],
    preserveState: true,
    preserveScroll: true,
  });
};

watch(() => props.term, reloadEateries);

watch(sortOption, () => {
  router.reload({
    only: ['eateries', 'sort'],
    reset: ['eateries'],
    data: { sort: sortOption.value },
  });
});
</script>

<template>
  <SearchResultsHeading
    :term="term"
    :range="range"
    :image="image"
    :latlng="latlng"
  />

  <Card
    v-if="eateries.data.length"
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

  <SidebarLayout>
    <template #sidebar>
      <TownFilterSidebar
        :filters="filters"
        fixed
        @filters-updated="handleFiltersChanged"
        @sidebar-closed="reloadEateries"
      />
    </template>

    <div
      ref="placeList"
      class="flex flex-col"
    >
      <Info
        v-if="county"
        class="mb-4"
      >
        <p class="prose prose-lg max-w-none">
          It looks like you're looking for places to eat in {{ county.name }},
          you can get more detailed results on the dedicated
          <Link :href="county.link">
            {{ county.name }} page in my eating out guide.
          </Link>
        </p>
      </Info>

      <template v-if="eateries.data.length">
        <Info
          no-icon
          class="mb-4 !border-0 !py-4 text-center font-semibold !shadow-none"
        >
          Found {{ eateries.total }} {{ pluralise('result', eateries.total) }}
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
      </template>

      <InfiniteScroll
        data="eateries"
        only-next
        preserve-url
        class="flex flex-col space-y-4"
      >
        <EateryCard
          v-for="eatery in eateries.data"
          :key="eatery.link"
          :eatery="eatery"
        />

        <Card
          v-if="!eateries.data.length"
          class="px-8 py-8 text-center text-xl"
        >
          No eateries found, try updating your filters or your search term!
        </Card>
      </InfiniteScroll>
    </div>
  </SidebarLayout>

  <JumpToContentButton
    v-if="placeList"
    :anchor="placeList"
    label="Jump to Results"
    side="left"
  />
</template>
