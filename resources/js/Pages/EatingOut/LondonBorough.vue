<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  LondonBoroughPage,
  MagicRouteGuide,
  NearbyCounty,
} from '@/types/EateryTypes';
import TownHeading from '@/Components/PageSpecific/EatingOut/Town/TownHeading.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import { computed, ref, useTemplateRef } from 'vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Info from '@/Components/Info.vue';
import useScreensize from '@/composables/useScreensize';
import useJourneyTracking from '@/composables/useJourneyTracking';
import SubHeading from '@/Components/SubHeading.vue';

const props = defineProps<{
  borough: LondonBoroughPage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
  nearby: NearbyCounty[];
  guides: MagicRouteGuide[];
}>();

const areaList = ref<HTMLElement | null>(null);
const areaSearch = ref('');

const sortOptions = ref<FormSelectOption[]>([
  { label: 'Alphabetically', value: 'alphabetical' },
  { label: 'Total Eateries', value: 'eateries' },
]);

const currentSort = ref('alphabetical');

const filteredAreas = computed(() => {
  const areas = props.borough.areas.filter((area) =>
    area.name.toLowerCase().includes(areaSearch.value.toLowerCase()),
  );

  if (currentSort.value === 'eateries') {
    return [...areas].sort((a, b) => b.total_eateries - a.total_eateries);
  }

  return areas;
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('areaList'),
  'scrolled_into_view',
  'WhereToEatIndexBoroughList',
  {
    borough: props.borough.name,
  },
);
</script>

<template>
  <TownHeading
    :county="borough.county"
    :image="borough.image"
    :name="borough.name"
    :latlng="borough.latlng"
    london-borough
  />

  <Card class="mt-3 flex flex-col space-y-4">
    <div
      class="prose-md prose max-w-none lg:my-0! lg:prose-lg *:first:lg:mt-0"
      v-html="borough.intro_text"
    />
  </Card>

  <div
    class="flex flex-col justify-between space-y-4 xmd:flex-row-reverse xmd:space-y-0"
  >
    <div
      class="xmd:flex-shrink-none w-full xmd:ml-4 xmd:w-1/3 xmd:max-w-20 lg:max-w-24"
    >
      <Card
        v-if="guides.length > 0"
        class="flex flex-col space-y-3"
      >
        <SubHeading> Specialist guides in {{ borough.name }} </SubHeading>

        <p class="prose mt-4 max-w-none">
          Are you heading to {{ borough.name }}? Take a look at these specialist
          guides I've put together for eating gluten free across
          {{ borough.name }}!
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
        <SubHeading>Other boroughs nearby</SubHeading>

        <div class="mt-4 flex flex-col space-y-4">
          <div
            v-for="nearbyBorough in nearby"
            :key="nearbyBorough.link"
            class="group relative"
          >
            <Link
              class="absolute top-0 left-0 z-10 h-full w-full"
              :href="nearbyBorough.link"
            />

            <div class="relative overflow-hidden">
              <img
                :src="nearbyBorough.image"
                :alt="nearbyBorough.name"
                class="h-auto w-full"
              />

              <div
                class="absolute bottom-0 w-full bg-primary/80 px-8 py-2 text-center text-lg font-semibold transition group-hover:scale-110 group-hover:bg-primary/90"
                v-text="nearbyBorough.name"
              />
            </div>
          </div>
        </div>
      </Card>

      <TopPlaces
        v-if="topRated.length"
        :collapsible="false"
      >
        <template #title>
          Top rated places to eat gluten free in {{ borough.name }}
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
          Most rated places to eat gluten free in {{ borough.name }}
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
      <Card class="flex flex-col space-y-4">
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
            v-model="areaSearch"
            name="search"
            label=""
            :placeholder="`Search for an area in ${borough.name}...`"
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
        ref="areaList"
        class="group mt-3 grid gap-3"
      >
        <template
          v-for="(area, index) in filteredAreas"
          :key="area.name"
        >
          <CountyTown :town="area" />

          <div
            v-if="index > 0 && index % 3 === 0"
            class="content_mobile_hint"
          />
        </template>
      </div>
    </div>
  </div>

  <JumpToContentButton
    v-if="areaList"
    :anchor="areaList"
    label="Jump to Area List"
  />
</template>
