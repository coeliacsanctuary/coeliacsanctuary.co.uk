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
import SubHeading from '@/Components/SubHeading.vue';
import { ref } from 'vue';

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
      Our Where to Eat guide lists 1,000s of independent eateries all over the
      UK and Ireland that offer gluten free options or have a gluten free menu.
    </p>

    <p class="prose prose-lg max-w-none md:prose-xl">
      Most of the places to eat listed in our guide are contributed by people
      like you, other Coeliac's or people with a gluten intolerance who know of
      local places in their local area and are kind enough to let us know
      through our
      <a
        href="/wheretoeat/recommend-a-place"
        target="_blank"
        >recommend a place</a
      >
      form.
    </p>
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
    <Card class="mt-3 flex flex-col space-y-4">
      <SubHeading>
        Top rated places to eat gluten free around the UK and Ireland
      </SubHeading>

      <p class="prose prose-lg max-w-none md:prose-xl">
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
    </Card>
  </template>

  <template v-if="mostRated.length">
    <Card class="mt-3 flex flex-col space-y-4">
      <SubHeading>
        Most rated places to eat gluten free around the UK and Ireland
      </SubHeading>

      <p class="prose prose-lg max-w-none md:prose-xl">
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
    </Card>
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
