<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  NationwidePage,
} from '@/types/EateryTypes';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import NationwideEateryCard from '@/Components/PageSpecific/EatingOut/NationwideEateryCard.vue';
import Heading from '@/Components/Heading.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';

defineProps<{
  county: NationwidePage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Gluten Free Nationwide Chains in the UK </Heading>

    <p class="prose prose-lg max-w-none">
      Discover nationwide chain restaurants, cafés and pubs offering gluten free
      options across the UK. Whether you're living with coeliac disease or
      following a gluten free diet, our guide helps you find places to eat,
      complete with reviews, ratings and useful information from the gluten free
      community.
    </p>

    <p class="prose prose-lg max-w-none">
      Many of the venues featured in our Where to Eat guide have been
      recommended by people with coeliac disease or gluten intolerance who have
      first-hand experience dining there. Their recommendations help keep our
      guide accurate, up to date and useful for anyone looking for gluten free
      places to eat across the UK.
    </p>
  </Card>

  <template v-if="topRated.length">
    <TopPlaces>
      <template #title>Top Rated Gluten Free Chain Restaurants</template>

      <template #default>
        <p class="prose prose-lg max-w-none">
          These are the three highest rated nationwide chains in our Where to
          Eat guide, based on reviews from our community. If you're looking for
          trusted gluten free options, these cafés, pubs and restaurants
          consistently receive excellent feedback from people with coeliac
          disease and those following a gluten free diet.
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
      <template #title> Most Reviewed Gluten Free Chain Restaurants </template>

      <template #default>
        <p class="prose prose-lg max-w-none">
          These are the three most reviewed chain restaurants in our Where to
          Eat guide. With the highest number of community reviews, they're among
          the most popular places to eat for people with coeliac disease and
          those following a gluten free diet.
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

  <Card class="mt-3 flex flex-col space-y-4">
    <Heading :border="false"> List of Gluten Free Nationwide Chains </Heading>
  </Card>

  <div class="group grid gap-3 md:grid-cols-2">
    <NationwideEateryCard
      v-for="eatery in county.chains"
      :key="eatery.key"
      :eatery="eatery"
    />
  </div>
</template>
