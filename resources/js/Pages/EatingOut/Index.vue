<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  EateryCountryPropItem,
  EaterySimpleHomeResource,
  EateryStatistics,
} from '@/types/EateryTypes';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import EateryCountryCard from '@/Components/PageSpecific/EatingOut/Index/EateryCountryCard.vue';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import Heading from '@/Components/Heading.vue';
import { useTemplateRef } from 'vue';
import Info from '@/Components/Info.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import RecentlyAddedEateries from '@/Components/PageSpecific/EatingOut/Index/RecentlyAddedEateries.vue';
import EateryGuideStatistics from '@/Components/PageSpecific/EatingOut/Index/EateryGuideStatistics.vue';
import EaterySidebarCta from '@/Components/PageSpecific/EatingOut/Index/EaterySidebarCta.vue';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';

defineProps<{
  countries: EateryCountryPropItem[];
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
  recentlyAdded: EaterySimpleHomeResource[];
  statistics: EateryStatistics;
}>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('guide'),
  'scrolled_into_view',
  'WhereToEatIndexCountryList',
);
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>Gluten Free Places to Eat and Visit</Heading>

    <p class="prose prose-lg max-w-none md:prose-xl">
      Our Where to Eat guide is a comprehensive resource featuring thousands of
      independent eateries across the UK and Ireland that cater to gluten free
      diners. Whether you're looking for a dedicated gluten free restaurant, a
      café with gluten free options, or a pub that offers a full gluten free
      menu, our guide helps you find safe and delicious places to eat. We
      include a diverse range of establishments, from cozy local bakeries to
      fine dining restaurants, ensuring that wherever you are, you can enjoy a
      great meal without worry.
    </p>

    <Info class="flex">
      <div class="inline-flex flex-col sm:flex-row sm:items-center">
        <p class="prose max-w-none md:prose-lg">
          Most of the eateries in our Where to Eat guide are recommended by
          people like you—those with coeliac disease or gluten intolerance who
          know great local spots. If you know a place we’ve missed, let us know
          and help grow our guide!
        </p>

        <div class="flex items-center justify-center">
          <CoeliacButton
            theme="secondary"
            size="md"
            :as="Link"
            href="/wheretoeat/recommend-a-place"
            label="Recommend a Place"
            classes="font-semibold justify-center mt-2 sm:mt-0 sm:ml-2 sm:min-w-[230px]"
          />
        </div>
      </div>
    </Info>
  </Card>

  <LocationSearch />

  <SidebarLayout content-first>
    <template #sidebar>
      <TopPlaces
        v-if="topRated.length"
        :collapsible="false"
      >
        <template #title>
          Top rated places to eat gluten free around the UK and Ireland
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
          Most rated places to eat gluten free around the UK and Ireland
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

      <RecentlyAddedEateries
        v-if="recentlyAdded.length"
        :eateries="recentlyAdded"
      />

      <EateryGuideStatistics :statistics="statistics" />

      <EaterySidebarCta
        icon="map"
        title="Browse the map"
        href="/wheretoeat/browse"
        label="View the map"
        identifier="WhereToEatIndexSidebar/Map"
      >
        Prefer to explore visually? Our interactive map plots every gluten free
        place in our guide, so you can see what's around you wherever you are.
      </EaterySidebarCta>

      <EaterySidebarCta
        icon="dinner"
        title="Nationwide chains"
        href="/wheretoeat/nationwide"
        label="View chains"
        identifier="WhereToEatIndexSidebar/Nationwide"
      >
        Looking for something reliable wherever you travel? Browse the chains
        with gluten free menus and coeliac procedures across the whole country.
      </EaterySidebarCta>

      <div class="content_hint"></div>
    </template>

    <Card
      ref="guide"
      class="flex flex-col space-y-4"
    >
      <Heading> Gluten Free around the UK and Ireland </Heading>

      <p class="prose prose-lg max-w-none md:prose-xl">
        Our gluten free eating out guide is organised by country, then broken
        down into counties and finally towns or cities, helping you easily find
        safe places to eat wherever you are. Whether you’re planning ahead or
        searching on the go, you can start by choosing a country below, where
        you’ll find popular counties highlighted first or the full list
        available to browse.
      </p>
    </Card>

    <div class="mt-3 flex flex-col space-y-3">
      <EateryCountryCard
        v-for="country in countries"
        :key="country.name"
        :country="country"
      />
    </div>
  </SidebarLayout>
</template>
