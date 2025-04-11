<script lang="ts" setup>
import { TownEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed } from 'vue';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import EateryIntroduction from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryIntroduction.vue';
import EateryReviews from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryReviews.vue';
import EateryInfoBlock from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryInfoBlock.vue';
import DynamicMap from '@/Components/Maps/DynamicMap.vue';

const props = defineProps<{ eatery: TownEatery }>();

const isNotNationwide = computed(() => {
  if (props.eatery.isNationwideBranch) {
    return true;
  }

  return props.eatery.county.id !== 1;
});

const eateryName = computed(() => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
});

const eateryLink = computed(() => {
  if (props.eatery.branch) {
    return props.eatery.branch.link;
  }

  return props.eatery.link;
});
</script>

<template>
  <Card>
    <div class="flex w-full">
      <div
        :class="{ 'sm:max-lg:w-3/5 lg:w-2/3': isNotNationwide }"
        class="flex w-full flex-col"
      >
        <EateryIntroduction
          :cuisine="eatery.cuisine"
          :is-not-nationwide="isNotNationwide"
          :link="eateryLink"
          :name="eateryName"
          :type="eatery.type"
          :venue-type="eatery.venue_type"
          :website="eatery.website"
          :is-branch="eatery.isNationwideBranch"
        />

        <EateryInfoBlock
          :address="
            eatery.branch
              ? eatery.branch.location.address
              : eatery.location.address
          "
          :info="eatery.info"
          :is-not-nationwide="isNotNationwide"
          :phone="eatery.phone"
          :restaurants="eatery.restaurants"
        />

        <p
          v-if="eatery.distance"
          class="prose-md prose max-w-none font-semibold"
        >
          Around {{ eatery.distance }} miles away...
        </p>
      </div>

      <div
        v-if="isNotNationwide"
        class="hidden pl-4 sm:block sm:max-lg:w-2/5 lg:w-1/3"
      >
        <StaticMap
          :lat="
            eatery.branch ? eatery.branch.location.lat : eatery.location.lat
          "
          :lng="
            eatery.branch ? eatery.branch.location.lng : eatery.location.lng
          "
          :title="`${eatery.branch && eatery.branch.name ? eatery.branch.name + ' - ' : ''} ${eatery.name} - ${eatery.branch ? eatery.branch.location.address : eatery.location.address}`"
        />
      </div>
    </div>

    <EateryReviews
      :link="eateryLink"
      :name="eateryName"
      :reviews="eatery.reviews"
      :is-branch="eatery.isNationwideBranch"
    />
  </Card>
</template>
