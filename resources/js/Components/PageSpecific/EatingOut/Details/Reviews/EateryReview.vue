<script setup lang="ts">
import { formatDate, ucfirst } from '@/helpers';
import StarRating from '@/Components/StarRating.vue';
import ReviewImageGallery from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageGallery.vue';
import { EateryReview } from '@/types/EateryTypes';

defineProps<{
  review: EateryReview;
  eateryName: string;
  showingAllReviews: boolean;
}>();

const reviewHasRatings = (review: EateryReview): boolean => {
  if (review?.expense) {
    return true;
  }

  if (review?.food_rating) {
    return true;
  }

  if (review?.service_rating) {
    return true;
  }

  return false;
};

const howExpensive = (review: EateryReview) => {
  let rtr = '';

  for (let x = 0; x < parseInt(<string>review.expense?.value, 10); x += 1) {
    rtr += '£';
  }

  return `<strong class="shrink-0">Price ${rtr}:</strong> <span>${review.expense?.label}</span>`;
};
</script>

<template>
  <div class="py-6">
    <div class="flex items-center justify-between">
      <div class="flex flex-col">
        <div class="flex flex-wrap items-center gap-2">
          <h4
            class="font-bold md:text-lg"
            v-text="review.body ? review.name : 'Anonymous'"
          />
          <span
            v-if="review.admin_review"
            class="block rounded-full border border-secondary bg-secondary/50 px-2 py-1 text-xs font-semibold"
          >
            Coeliac Sanctuary
          </span>
        </div>

        <time
          class="text-xs text-grey-dark"
          :datetime="review.published"
          :title="formatDate(review.published, 'Do MMM YYYY h:mm a')"
          v-text="review.date_diff"
        />
      </div>

      <div class="mt-1 flex items-center">
        <StarRating
          :rating="review.rating"
          size="w-4 h-4 xs:w-5 xs:h-5"
          show-all
        />
      </div>
    </div>

    <div
      class="prose mt-2 max-w-none"
      v-html="
        review.body
          ? review.body
          : `<em>Customer didn't leave a review with their rating</em>`
      "
    />

    <ReviewImageGallery
      v-if="review.images.length"
      class="mt-4"
      :eatery-name="eateryName"
      :images="review.images"
    />

    <div>
      <ul
        v-if="reviewHasRatings(review)"
        class="mt-3 grid grid-cols-1 gap-2 text-sm xxs:grid-cols-2 xl:grid-cols-4"
      >
        <li
          v-if="review.branch_name && showingAllReviews"
          class="flex flex-col gap-0.5 rounded-sm bg-primary-light/50 px-3 py-2 leading-none"
        >
          <strong>Location:</strong>
          <span v-text="ucfirst(review.branch_name)" />
        </li>

        <li
          v-if="review.expense"
          class="flex flex-col gap-0.5 rounded-sm bg-primary-light/50 px-3 py-2 leading-none"
          v-html="howExpensive(review)"
        />

        <li
          v-if="review.food_rating"
          class="flex flex-col gap-0.5 rounded-sm bg-primary-light/50 px-3 py-2 leading-none"
        >
          <strong>Food:</strong>
          <span v-text="ucfirst(review.food_rating)" />
        </li>

        <li
          v-if="review.service_rating"
          class="flex flex-col gap-0.5 rounded-sm bg-primary-light/50 px-3 py-2 leading-none"
        >
          <strong>Service:</strong>
          <span v-text="ucfirst(review.service_rating)" />
        </li>
      </ul>
    </div>
  </div>
</template>
