<script lang="ts" setup>
import {
  DetailedEatery,
  EateryReview as EateryReviewType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ComputedRef, ref, watch } from 'vue';
import RatingsBreakdown from '@/Components/PageSpecific/Shared/RatingsBreakdown.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import EateryAddReview from '@/Components/PageSpecific/EatingOut/Details/Reviews/EateryAddReview.vue';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import { router } from '@inertiajs/vue3';
import EateryReview from '@/Components/PageSpecific/EatingOut/Details/Reviews/EateryReview.vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const eateryName = (): string => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};

const hideReviewsWithoutBody = ref(true);
const showAllReviews = ref(false);
const reviewFilter = ref<undefined | StarRatingType>(undefined);

const reviews: ComputedRef<EateryReviewType[]> = computed(
  () => props.eatery.reviews.user_reviews,
);

const filteredReviews: ComputedRef<EateryReviewType[]> = computed(() => {
  let thisReviews = reviews.value;

  if (reviewFilter.value) {
    thisReviews = thisReviews.filter(
      (review) => review.rating === reviewFilter.value,
    );
  }

  if (!hideReviewsWithoutBody.value) {
    return thisReviews;
  }

  return thisReviews.filter((review) => review.body);
});

const displayAddReviewModal = ref(false);

watch(showAllReviews, (newValue) => {
  router.reload({
    data: { 'show-all-reviews': newValue },
    only: ['eatery'],
    replace: true,
  });
});
</script>

<template>
  <Card class="lg:rounded-lg lg:p-8">
    <div
      class="mx-auto md:grid md:gap-x-8 md:max-xl:grid-cols-3 xl:grid-cols-4"
    >
      <RatingsBreakdown
        :average="eatery.reviews.average"
        :breakdown="eatery.reviews.ratings"
        :count="eatery.reviews.number"
        :can-add-review="!eatery.closed_down"
        :filtered-on="reviewFilter"
        filterable
        @create-review="displayAddReviewModal = true"
        @filter="
          (rating: StarRatingType) =>
            (reviewFilter = reviewFilter === rating ? undefined : rating)
        "
      >
        Have you visited <strong v-text="eateryName()" />? Share your experience
        with other people!
      </RatingsBreakdown>

      <div class="mt-8 md:col-span-2 md:mt-0 xl:col-span-3">
        <div class="flow-root">
          <div
            v-if="reviews.length > 0 || eatery.branch"
            class="mb-2 flex w-auto flex-col justify-between space-y-4 rounded-sm bg-primary-light/50 px-3 py-1 sm:flex-row sm:space-y-0 sm:space-x-16"
          >
            <div
              class="flex-1"
              :class="{ 'flex justify-between': eatery.branch }"
            >
              <FormCheckbox
                v-model="hideReviewsWithoutBody"
                name="hide-ratings"
                label="Hide ratings without a review"
                :disabled="reviews.length === 0"
                class="w-full sm:w-auto"
              />
            </div>
            <div
              v-if="eatery.branch"
              class="flex flex-1 sm:justify-end"
            >
              <FormCheckbox
                v-model="showAllReviews"
                name="show-all-reviews"
                label="Show reviews for all branches"
                class="w-full sm:w-auto"
              />
            </div>
          </div>

          <div class="-my-6 divide-y divide-gray-200">
            <template
              v-if="!hideReviewsWithoutBody || filteredReviews.length > 0"
            >
              <EateryReview
                v-for="review in filteredReviews"
                :key="review.id"
                :review="review"
                :eatery-name="eateryName()"
                :showing-all-reviews="!eatery.branch || showAllReviews"
              />
            </template>

            <div
              v-else
              class="py-6 text-lg"
            >
              No reviews found...
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal
      :open="displayAddReviewModal"
      size="large"
      @close="displayAddReviewModal = false"
    >
      <EateryAddReview :eatery="eatery" />
    </Modal>
  </Card>
</template>
