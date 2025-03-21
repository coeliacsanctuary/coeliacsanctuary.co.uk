<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  CountyPage,
} from '@/types/EateryTypes';
import CountyHeading from '@/Components/PageSpecific/EatingOut/County/CountyHeading.vue';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import CountyTown from '@/Components/PageSpecific/EatingOut/County/CountyTown.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import Heading from '@/Components/Heading.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import { Link } from '@inertiajs/vue3';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import Info from '@/Components/Info.vue';

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
  />

  <template v-if="topRated.length">
    <TopPlaces>
      <template #title>
        Top rated places to eat gluten free in {{ county.name }}
      </template>

      <template #default>
        <p class="prose prose-lg max-w-none mb-2">
          Discover the best rated places to eat gluten free in
          <span
            class="font-semibold"
            v-text="county.name"
          />, voted by people like you! From cozy cafes to restaurants, these
          establishments offer exceptional gluten-free options. Enjoy a
          delightful meal or snack, tailored to your dietary needs.
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
        Most rated places to eat gluten free in {{ county.name }}
      </template>

      <template #default>
        <p class="prose prose-lg max-w-none mb-2">
          Discover the most reviewed and highly praised places to eat gluten
          free in
          <span
            class="font-semibold"
            v-text="county.name"
          />, loved by people just like you! These establishments have garnered
          a significant number of reviews, ensuring a great gluten free
          experience.
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
    <Heading> Gluten Free {{ county.name }} </Heading>

    <p class="prose prose-lg max-w-none">
      If you're heading to <span class="font-semibold">{{ county.name }}</span
      >, our eating out guide lists all the gluten free places in the towns,
      villages, and cities throughout the region. Explore the gluten-free
      options in <span class="font-semibold">{{ county.name }}s</span> diverse
      culinary scene.
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

    <GoogleAd code="5284484376" />

    <div class="group grid gap-3 md:grid-cols-3">
      <CountyTown
        v-for="town in county.towns"
        :key="town.name"
        :town="town"
      />
    </div>
  </Card>
</template>
