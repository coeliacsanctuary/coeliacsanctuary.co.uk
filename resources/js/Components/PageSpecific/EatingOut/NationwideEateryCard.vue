<script lang="ts" setup>
import { NationwideEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed } from 'vue';
import EateryIntroduction from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryIntroduction.vue';
import EateryReviews from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryReviews.vue';
import EateryInfoBlock from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryInfoBlock.vue';

const props = defineProps<{ eatery: NationwideEatery }>();

const eateryName = computed(() => props.eatery.name);

const eateryLink = computed(() => props.eatery.link);
</script>

<template>
  <Card class="flex flex-col justify-between">
    <div class="flex w-full">
      <div class="flex w-full flex-col">
        <EateryIntroduction
          :cuisine="eatery.cuisine"
          :is-not-nationwide="false"
          :link="eateryLink"
          :name="eateryName"
          :type="eatery.type"
          :venue-type="eatery.venue_type"
          :website="eatery.website"
          :is-branch="false"
          :is-fully-gf="eatery.is_fully_gf"
        />

        <EateryInfoBlock
          address=""
          :info="eatery.info"
          :is-not-nationwide="false"
          :phone="eatery.phone"
          :restaurants="[]"
        />
      </div>
    </div>

    <EateryReviews
      :link="eateryLink"
      :name="eateryName"
      :reviews="eatery.reviews"
    />
  </Card>
</template>
