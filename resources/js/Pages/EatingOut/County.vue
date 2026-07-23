<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  CountyPage,
  CountyPageTown,
  LondonPage,
  LondonPageBorough,
} from '@/types/EateryTypes';
import CountyHeading from '@/Components/PageSpecific/EatingOut/County/CountyHeading.vue';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';
import Heading from '@/Components/Heading.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Info from '@/Components/Info.vue';
import { computed, ref, useTemplateRef } from 'vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import useScreensize from '@/composables/useScreensize';
import LondonBorough from '@/Components/PageSpecific/EatingOut/County/LondonBorough.vue';

const props = defineProps<{
  county: CountyPage | LondonPage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();

const townList = ref<HTMLElement | null>(null);
const townSearch = ref('');

const sortOptions = ref<FormSelectOption[]>([
  { label: 'Alphabetically', value: 'alphabetical' },
  { label: 'Total Eateries', value: 'eateries' },
]);

const currentSort = ref('alphabetical');

const filteredTowns = computed(() => {
  let towns: CountyPageTown[] | LondonPageBorough[] = props.county.towns;

  if ((<LondonPage>props.county).boroughs) {
    towns = (<LondonPage>props.county).boroughs;
  }

  towns = towns.filter((town) =>
    town.name.toLowerCase().includes(townSearch.value.toLowerCase()),
  );

  if (currentSort.value === 'eateries') {
    return [...towns].sort((a, b) => b.total_eateries - a.total_eateries);
  }

  return towns;
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('townList'),
  'scrolled_into_view',
  'WhereToEatIndexCountyList',
  {
    county: props.county.name,
  },
);
</script>

<template>
  <CountyHeading
    :eateries="county.eateries"
    :latlng="county.latlng"
    :image="county.image"
    :name="county.name"
    :reviews="county.reviews"
    :towns="county.name === 'London' ? 0 : county.towns.length"
    :hide-towns="county.name === 'London'"
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Gluten Free {{ county.name }} </Heading>

    <p
      class="prose prose-lg max-w-none lg:prose-xl"
      v-html="county.description"
    />
  </Card>

  <div
    class="flex flex-col justify-between space-y-4 xmd:flex-row-reverse xmd:space-y-0"
  >
    <div
      class="xmd:flex-shrink-none w-full xmd:ml-4 xmd:w-1/3 xmd:max-w-20 lg:max-w-24"
    >
      <TopPlaces
        v-if="topRated.length"
        :collapsible="false"
      >
        <template #title>
          Top rated places to eat gluten free in {{ county.name }}
        </template>

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
          Most rated places to eat gluten free in {{ county.name }}
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

      <div class="content_hint"></div>
    </div>

    <div class="flex-1">
      <Card class="mt-3 flex flex-col space-y-4">
        <Info class="flex">
          <div class="inline-flex flex-col">
            <p class="prose prose-sm max-w-none sm:prose-lg md:prose-xl">
              Most of the eateries in our Where to Eat guide are recommended by
              people like you—those with coeliac disease or gluten intolerance
              who know great local spots. If you know a place we’ve missed, let
              us know and help grow our guide!
            </p>

            <div class="mt-4 flex items-center justify-center">
              <CoeliacButton
                theme="secondary"
                :size="useScreensize().screenIsGreaterThan('sm') ? 'lg' : 'md'"
                :as="Link"
                href="/wheretoeat/recommend-a-place"
                label="Recommend a Place"
                classes="font-semibold justify-center mt-2 sm:mt-0 sm:ml-2 sm:min-w-[230px]"
              />
            </div>
          </div>
        </Info>

        <div class="content_hint"></div>

        <div
          class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0 sm:space-x-4"
        >
          <FormInput
            v-model="townSearch"
            name="search"
            label=""
            :placeholder="
              county.name === 'London'
                ? 'Search for a borough in London...'
                : `Search for a town in ${county.name}...`
            "
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
        ref="townList"
        class="group mt-3 grid gap-3"
      >
        <template
          v-for="(town, index) in filteredTowns"
          :key="town.name"
        >
          <CountyTown
            v-if="county.name !== 'London'"
            :town="town"
          />

          <LondonBorough
            v-if="county.name === 'London'"
            :borough="town as LondonPageBorough"
          />

          <div
            v-if="index > 0 && index % 3 === 0"
            class="content_mobile_hint"
          />
        </template>
      </div>
    </div>
  </div>

  <JumpToContentButton
    v-if="townList"
    :anchor="townList"
    :label="
      county.name === 'London' ? 'Jump to borough list' : 'Jump to Towns List'
    "
  />
</template>
