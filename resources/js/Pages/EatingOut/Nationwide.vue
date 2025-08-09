<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  NationwidePage,
} from '@/types/EateryTypes';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import NationwideEateryCard from '@/Components/PageSpecific/EatingOut/NationwideEateryCard.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import Heading from '@/Components/Heading.vue';
import SubHeading from '@/Components/SubHeading.vue';

defineProps<{
  county: NationwidePage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Gluten Free Nationwide Chains </Heading>

    <p class="prose prose-lg max-w-none">
      Here you can see all of the nationwide chains in our Where to Eat guide
      that offer gluten free options across the UK.
    </p>

    <p class="prose prose-lg max-w-none">
      Most of the places to eat listed in our guide are contributed by people
      like you, other Coeliac's or people with a gluten intolerance who know of
      local places in their local area and are kind enough to let us know.
    </p>
  </Card>

  <template v-if="topRated.length">
    <Card class="mt-3 flex flex-col space-y-4">
      <SubHeading> Highest Rated Gluten Free Nationwide Chains </SubHeading>

      <p class="prose prose-lg max-w-none">
        Discover the best rated places nationwide chains to eat gluten free in,
        voted by people like you! From cozy cafes to restaurants, these
        establishments offer exceptional gluten-free options. Enjoy a delightful
        meal or snack, tailored to your dietary needs.
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
      <SubHeading> Most rated nationwide chains to eat gluten free </SubHeading>

      <p class="prose prose-lg max-w-none">
        Discover the most reviewed and highly praised chains to eat gluten free
        around the UK, loved by people just like you! These establishments have
        garnered a significant number of reviews, ensuring a great gluten free
        experience.
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

  <GoogleAd
    :key="$page.url"
    code="5284484376"
  />

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
