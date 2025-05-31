<script lang="ts" setup>
import {
  EateryReview,
  StarRating as StarRatingType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ref } from 'vue';
import StarRating from '@/Components/StarRating.vue';
import ReviewImageGallery from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageGallery.vue';
import { ucfirst } from '../../../../helpers';

const props = defineProps<{
  eateryName: string;
  review: EateryReview;
}>();

const hasRatings = computed(() => {
  if (props.review?.expense) {
    return true;
  }

  if (props.review?.food_rating) {
    return true;
  }

  if (props.review?.service_rating) {
    return true;
  }

  return false;
});

const howExpensive = () => {
  let rtr = '';

  for (
    let x = 0;
    x < parseInt(<string>props.review.expense?.value, 10);
    x += 1
  ) {
    rtr += '£';
  }

  return `<strong class="flex-shrink-0">${rtr}</strong> <span>${props.review.expense?.label}</span>`;
};

const displayFullReview = ref(false);

const reviewBody = computed(() => {
  let { body } = props.review;

  if (body && body.length > 500 && !displayFullReview.value) {
    body = `${body.substring(0, body.indexOf(' ', 500))}...`;
  }

  return body?.replaceAll('\n', '<br />');
});
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <h3 class="text-lg font-semibold lg:text-2xl">Our Review</h3>

    <div class="flex flex-col space-y-2 lg:space-y-4">
      <ul
        v-if="hasRatings"
        class="mb-2 grid grid-cols-1 gap-1 leading-none xs:grid-cols-2 xs:gap-3 xmd:grid-cols-4 lg:text-lg"
      >
        <li
          class="flex items-center space-x-2 rounded-sm bg-primary-light/50 px-3 py-2"
        >
          <strong class="flex-shrink-0">Our Rating</strong>
          <StarRating
            :rating="<StarRatingType>review.rating"
            size="w-4 h-4"
            show-all
          />
        </li>

        <li
          v-if="review.expense"
          class="flex items-center space-x-2 rounded-sm bg-primary-light/50 px-3 py-2"
          v-html="howExpensive()"
        />
        <li
          v-if="review.food_rating"
          class="flex items-center space-x-2 rounded-sm bg-primary-light/50 px-3 py-2"
        >
          <strong>Food</strong> <span>{{ ucfirst(review.food_rating) }}</span>
        </li>
        <li
          v-if="review.service_rating"
          class="flex items-center space-x-2 rounded-sm bg-primary-light/50 px-3 py-2"
        >
          <strong>Service</strong>
          <span>{{ ucfirst(review.service_rating) }}</span>
        </li>
      </ul>

      <p
        class="prose max-w-none md:max-xl:prose-lg xl:prose-xl"
        v-html="reviewBody"
      />

      <template v-if="review.body?.length > 500 && !displayFullReview">
        <a
          class="cursor-pointer text-lg font-semibold text-primary-dark hover:underline xl:text-xl"
          @click.prevent="displayFullReview = true"
          v-text="'Read our full review!'"
        />
      </template>

      <div
        v-if="review.images"
        class="flex flex-col rounded-sm border border-primary bg-primary-light/25 p-2"
      >
        <p class="text-md font-semibold lg:text-lg">Our Photos</p>

        <ReviewImageGallery
          :images="review.images"
          :eatery-name="eateryName"
          with-margin
        />
      </div>
    </div>
  </Card>
</template>
