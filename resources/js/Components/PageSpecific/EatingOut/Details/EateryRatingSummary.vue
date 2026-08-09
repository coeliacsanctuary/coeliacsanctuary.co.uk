<script lang="ts" setup>
import {
  DetailedEatery,
  StarRating as StarRatingType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import StarRating from '@/Components/StarRating.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { computed } from 'vue';
import { pluralise } from '@/helpers';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

defineEmits(['goToReview']);

const averageRating = computed(
  () => parseFloat(props.eatery.reviews.average) as StarRatingType,
);

const eateryName = (): string =>
  props.eatery.branch && props.eatery.branch.name
    ? `${props.eatery.branch.name} - ${props.eatery.name}`
    : props.eatery.name;
</script>

<template>
  <Card class="flex flex-col space-y-4">
    <div
      v-if="eatery.reviews.number > 0"
      class="flex flex-col space-y-2"
    >
      <StarRating
        :rating="averageRating"
        show-all
        align="start"
        size="size-8 md:size-10"
      />

      <p class="text-grey-dark">
        <strong class="text-xl text-grey-darkest">
          {{ eatery.reviews.average }}
        </strong>
        from
        <strong>
          {{ eatery.reviews.number }}
          {{ pluralise('review', eatery.reviews.number) }}
        </strong>
      </p>
    </div>

    <p
      v-else
      class="text-sm text-grey-dark italic"
    >
      {{ eateryName() }} hasn't been rated yet.
    </p>

    <div
      v-if="!eatery.closed_down"
      class="space-y-3"
    >
      <h3 class="text-lg font-semibold">Share your thoughts</h3>

      <p class="text-sm">
        Have you visited <strong v-text="eateryName()" />? Share your experience
        with other people!
      </p>

      <CoeliacButton
        label="Write a review"
        theme="light"
        size="xl"
        as="button"
        type="button"
        @click="$emit('goToReview')"
      />
    </div>
  </Card>
</template>
