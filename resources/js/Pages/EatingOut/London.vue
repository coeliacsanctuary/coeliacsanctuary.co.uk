<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import {
  CountyEatery as CountyEateryType,
  LondonPage,
} from '@/types/EateryTypes';
import Heading from '@/Components/Heading.vue';
import CountyHeading from '@/Components/PageSpecific/EatingOut/County/CountyHeading.vue';
import CountyEatery from '@/Components/PageSpecific/EatingOut/County/CountyEatery.vue';
import TopPlaces from '@/Components/PageSpecific/EatingOut/Index/TopPlaces.vue';
import { Link } from '@inertiajs/vue3';
import Info from '@/Components/Info.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import LondonBorough from '@/Components/PageSpecific/EatingOut/County/LondonBorough.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import { computed, ref } from 'vue';
import JumpToContentButton from '@/Components/JumpToContentButton.vue';
import FormInput from '@/Components/Forms/FormInput.vue';
import FormSelect from '@/Components/Forms/FormSelect.vue';
import { FormSelectOption } from '@/Components/Forms/Props';

const props = defineProps<{
  london: LondonPage;
  topRated: CountyEateryType[];
  mostRated: CountyEateryType[];
}>();

const boroughList = ref<HTMLElement | null>(null);
const boroughSearch = ref('');

const sortOptions = ref<FormSelectOption[]>([
  { label: 'Alphabetically', value: 'alphabetical' },
  { label: 'Total Eateries', value: 'eateries' },
]);

const currentSort = ref('alphabetical');

const filteredBoroughs = computed(() => {
  const boroughs = props.london.boroughs.filter((borough) =>
    borough.name.toLowerCase().includes(boroughSearch.value.toLowerCase()),
  );

  if (currentSort.value === 'eateries') {
    return [...boroughs].sort((a, b) => b.locations - a.locations);
  }

  return boroughs;
});
</script>

<template>
  <CountyHeading
    :eateries="london.eateries"
    :latlng="london.latlng"
    :image="london.image"
    :name="london.name"
    :reviews="london.reviews"
    :towns="0"
    hide-towns
  />

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
      <span class="font-semibold">London</span>. But don't worry, we’ve got you
      covered.
    </p>

    <p class="prose prose-lg max-w-none">
      From fully gluten free bakeries and restaurants to mainstream venues with
      clear allergen labelling and dedicated prep processes,
      <span class="font-semibold">London</span> has something for everyone.
      We've gathered the best gluten free eateries across the capital, so you
      can eat out with confidence.
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

  <template v-if="topRated.length">
    <TopPlaces>
      <template #title>
        Top rated places to eat gluten free in London
      </template>

      <template #default>
        <p class="prose prose-lg mb-2 md:max-w-none">
          Discover the best rated places to eat gluten free in
          <span
            class="font-semibold"
            v-text="london.name"
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
        Most rated places to eat gluten free in London
      </template>

      <template #default>
        <p class="prose prose-lg mb-2 max-w-none">
          Discover the most reviewed and highly praised places to eat gluten
          free in
          <span
            class="font-semibold"
            v-text="london.name"
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

  <GoogleAd
    :key="$page.url"
    code="5284484376"
  />

  <div ref="boroughList">
    <Card class="mx-4 mb-4">
      <div class="flex items-center justify-between">
        <FormInput
          v-model="boroughSearch"
          name="search"
          label=""
          placeholder="Search for a borough in London..."
          hide-label
          borders
          class="w-full max-w-md"
        />

        <FormSelect
          v-model="currentSort"
          name="sort"
          :options="sortOptions"
          label="Sort by"
          borders
          class="flex items-center space-x-2 xs:flex-col xs:items-start xs:space-x-0 sm:flex-row sm:items-center sm:space-x-2"
          size="small"
        />
      </div>
    </Card>

    <div class="mt-2 grid gap-4 px-4 md:grid-cols-2">
      <LondonBorough
        v-for="borough in filteredBoroughs"
        :key="borough.name"
        :borough="borough"
      />
    </div>
  </div>

  <JumpToContentButton
    v-if="boroughList"
    :anchor="boroughList"
    label="Jump to Borough List"
  />
</template>
