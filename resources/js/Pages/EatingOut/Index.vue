<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';
import {
  CountyEatery as CountyEateryType,
  EateryCountryListProp,
} from '@/types/EateryTypes';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import EateryCountryCard from '@/Components/PageSpecific/EatingOut/Index/EateryCountryCard.vue';
import LocationSearch from '@/Components/PageSpecific/EatingOut/LocationSearch.vue';
import Heading from '@/Components/Heading.vue';
import { ref } from 'vue';
import Info from '@/Components/Info.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { Link } from '@inertiajs/vue3';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';

defineProps<{
  countries: EateryCountryListProp;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();

const guide = ref<null | { $el: Element }>(null);
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
        <p class="prose prose-lg max-w-none md:prose-xl">
          Most of the eateries in our Where to Eat guide are recommended by
          people like you—those with coeliac disease or gluten intolerance who
          know great local spots. If you know a place we’ve missed, let us know
          and help grow our guide!
        </p>

        <div class="flex items-center justify-center">
          <CoeliacButton
            theme="secondary"
            size="xl"
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

  <Card class="mt-3 flex flex-col space-y-4">
    <a
      class="flex flex-col items-center justify-center space-y-4 text-center text-xl cursor-pointer"
      @click="guide?.$el.scrollIntoView({ behavior: 'smooth' })"
    >
      <p>Or just browse our Eating Out guide...</p>
      <ChevronDownIcon
        class="h-16 w-16 animate-bounce stroke-2 md:h-24 md:w-24"
      />
    </a>
  </Card>

  <template v-if="topRated.length">
    <TopPlaces>
      <template #title>
        Top rated places to eat gluten free around the UK and Ireland
      </template>

      <template #default>
        <p class="prose prose-lg max-w-none md:prose-xl mb-2">
          These are the top rated places to eat gluten free in our eating out
          guide, voted by people just like you!
        </p>

        <div class="group grid gap-3 md:grid-cols-3">
          <CountyEatery
            v-for="eatery in topRated"
            :key="eatery.name"
            :eatery="eatery"
          />
        </div>
      </template>
    </TopPlaces>
  </template>

  <template v-if="mostRated.length">
    <TopPlaces>
      <template #title>
        Most rated places to eat gluten free around the UK and Ireland
      </template>

      <template #default>
        <p class="prose prose-lg max-w-none md:prose-xl mb-2">
          These are the top gluten free places in our eating guide gluten that
          have had the most people leave reviews!
        </p>

        <div class="group grid gap-3 md:grid-cols-3">
          <CountyEatery
            v-for="eatery in mostRated"
            :key="eatery.name"
            :eatery="eatery"
          />
        </div>
      </template>
    </TopPlaces>
  </template>

  <Card
    ref="guide"
    class="mt-3 flex flex-col space-y-4"
  >
    <Heading> Gluten Free around the UK and Ireland </Heading>

    <p class="prose prose-lg max-w-none md:prose-xl">
      Our eating out guide is split into countries, counties and then towns or
      cities, click or tap on a country below to get started!
    </p>

    <div class="flex flex-col space-y-3">
      <EateryCountryCard
        v-for="(details, country) in countries"
        :key="country"
        :country="country"
        :details="details"
      />
    </div>
  </Card>
</template>
