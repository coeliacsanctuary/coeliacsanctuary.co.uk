<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyPage,
  CountyEatery as CountyEateryType,
} from '@/types/EateryTypes';
import Heading from '@/Components/Heading.vue';
import CountyHeading from '@/Components/PageSpecific/EatingOut/County/CountyHeading.vue';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import { Link } from '@inertiajs/vue3';
import Info from '@/Components/Info.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';

defineProps<{
  county: CountyPage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();
</script>

<template>
  <CountyHeading
    :eateries="county.eateries"
    :latlng="county.latlng"
    :image="county.image"
    :name="county.name"
    :reviews="county.reviews"
    :towns="county.towns.length"
    hide-towns
  />

  <div
    class="flex flex-col-reverse space-y-2 xmd:flex-row xmd:space-y-0 xmd:space-x-2"
  >
    <div class="xmd:w-2/3">
      <Card class="mt-3 flex flex-col space-y-4">
        <Heading as="h2">Gluten Free London</Heading>

        <p class="prose prose-xl max-w-none font-semibold">
          Looking for gluten free places to eat in London? You’re in the right
          place.
        </p>

        <p class="prose prose-lg max-w-none">
          Whether you're coeliac, gluten intolerant, or simply cutting back on
          gluten, finding safe and delicious spots to eat out can feel like a
          challenge - especially in a city as big as
          <span class="font-semibold">London</span>. But don't worry, we’ve got
          you covered.
        </p>

        <p class="prose prose-lg max-w-none">
          From fully gluten free bakeries and restaurants to mainstream venues
          with clear allergen labelling and dedicated prep processes,
          <span class="font-semibold">London</span> has something for everyone.
          We've gathered the best gluten free eateries across the capital, so
          you can eat out with confidence.
        </p>

        <Info class="flex">
          <div class="inline-flex flex-col sm:flex-row sm:items-center">
            <p class="prose prose-lg max-w-none md:prose-xl">
              Most of the eateries in our Where to Eat guide are recommended by
              people like you—those with coeliac disease or gluten intolerance
              who know great local spots. If you know a place we’ve missed, let
              us know and help grow our guide!
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

      <div class="group mt-2 grid gap-3 md:grid-cols-3">
        <CountyTown
          v-for="town in county.towns"
          :key="town.name"
          :town="town"
        />
      </div>
    </div>

    <div class="xmd:w-1/3">
      <template v-if="topRated.length">
        <TopPlaces show-breakpoint="xmd">
          <template #title>
            Top rated places to eat gluten free in {{ county.name }}
          </template>

          <template #default>
            <p class="prose prose-lg mb-2 max-w-none">
              Discover the best rated places to eat gluten free in
              <span
                class="font-semibold"
                v-text="county.name"
              />, voted by people like you! From cozy cafes to restaurants,
              these establishments offer exceptional gluten-free options. Enjoy
              a delightful meal or snack, tailored to your dietary needs.
            </p>

            <div class="group grid gap-3">
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
        <TopPlaces show-breakpoint="xmd">
          <template #title>
            Most rated places to eat gluten free in {{ county.name }}
          </template>

          <template #default>
            <p class="prose prose-lg mb-2 max-w-none">
              Discover the most reviewed and highly praised places to eat gluten
              free in
              <span
                class="font-semibold"
                v-text="county.name"
              />, loved by people just like you! These establishments have
              garnered a significant number of reviews, ensuring a great gluten
              free experience.
            </p>

            <div class="group grid gap-3">
              <CountyEatery
                v-for="eatery in mostRated"
                :key="eatery.name"
                :eatery="eatery"
              />
            </div>
          </template>
        </TopPlaces>
      </template>
    </div>
  </div>
</template>
