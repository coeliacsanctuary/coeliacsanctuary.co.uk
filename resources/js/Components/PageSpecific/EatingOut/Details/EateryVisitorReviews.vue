<script lang="ts" setup>
import { DetailedEatery, EateryReview } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { computed, ComputedRef, ref } from 'vue';
import StarRating from '@/Components/StarRating.vue';
import { formatDate, ucfirst } from '@/helpers';
import RatingsBreakdown from '@/Components/PageSpecific/Shared/RatingsBreakdown.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import EateryAddReview from '@/Components/PageSpecific/EatingOut/Details/Reviews/EateryAddReview.vue';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import ReviewImageGallery from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageGallery.vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const hideReviewsWithoutBody = ref(true);
const reviewFilter = ref<undefined | StarRatingType>(undefined);

const reviews: ComputedRef<EateryReview[]> = computed(
  () => props.eatery.reviews.user_reviews,
);

const filteredReviews: ComputedRef<EateryReview[]> = computed(() => {
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
  <Card class="lg:rounded-lg lg:p-8">
    <div
      class="mx-auto md:grid md:max-xl:grid-cols-3 md:gap-x-8 xl:grid-cols-4"
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
        Have you visited <strong v-text="eatery.name" />? Share your experience
        with other people!
      </RatingsBreakdown>

      <div class="mt-8 md:col-span-2 md:mt-0 xl:col-span-3">
        <div class="flow-root">
          <div
            v-if="reviews.length > 0"
            class="mb-2 w-auto rounded-sm bg-primary-light/50 px-3 py-1"
          >
            <FormCheckbox
              v-model="hideReviewsWithoutBody"
              name="hide-ratings"
              label="Hide ratings without a review"
            />
          </div>

          <div class="-my-6 divide-y divide-gray-200">
            <template
              v-if="!hideReviewsWithoutBody || filteredReviews.length > 0"
            >
              <div
                v-for="review in filteredReviews"
                :key="review.id"
                class="py-6"
              >
                <div class="flex items-center justify-between">
                  <div class="flex flex-col">
                    <h4
                      class="font-bold lg:text-xl"
                      v-text="review.body ? review.name : 'Anonymous'"
                    />
                    <time
                      :datetime="review.published"
                      :title="
                        formatDate(review.published, 'Do MMM YYYY h:mm a')
                      "
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
                  class="prose mt-2 max-w-none lg:prose-lg"
                  v-html="
                    review.body
                      ? review.body
                      : `<em>Customer didn't leave a review with their rating</em>`
                  "
                />

                <ReviewImageGallery
                  v-if="review.images.length"
                  class="mt-4"
                  :eatery-name="eatery.name"
                  :images="review.images"
                />

                <div>
                  <ul
                    v-if="reviewHasRatings(review)"
                    class="mt-3 grid grid-cols-1 gap-3 sm:text-lg"
                    :class="
                      review.branch_name ? 'sm:grid-cols-4' : 'sm:grid-cols-3'
                    "
                  >
                    <li
                      v-if="review.branch_name"
                      class="rounded-sm bg-primary-light/50 px-3 py-2 leading-none flex space-x-2 sm:flex-col sm:max-md:space-y-1 sm:max-xl:space-x-0 md:max-xl:space-y-2 xl:flex-row xl:space-y-0 xl:space-x-2"
                    >
                      <strong>Location:</strong>
                      <span v-text="ucfirst(review.branch_name)" />
                    </li>
                    <li
                      v-if="review.expense"
                      class="rounded-sm bg-primary-light/50 px-3 py-2 leading-none flex space-x-2 sm:flex-col sm:max-md:space-y-1 sm:max-xl:space-x-0 md:max-xl:space-y-2 xl:flex-row xl:space-y-0 xl:space-x-2"
                      v-html="howExpensive(review)"
                    />
                    <li
                      v-if="review.food_rating"
                      class="rounded-sm bg-primary-light/50 px-2 py-2 leading-none flex space-x-2 sm:flex-col sm:max-md:space-y-1 sm:max-xl:space-x-0 md:max-xl:space-y-2 xl:flex-row xl:space-y-0 xl:space-x-2"
                    >
                      <strong>Food:</strong>
                      <span v-text="ucfirst(review.food_rating)" />
                    </li>
                    <li
                      v-if="review.service_rating"
                      class="rounded-sm bg-primary-light/50 px-2 py-2 leading-none flex space-x-2 sm:flex-col sm:space-y-1 sm:space-x-0 md:space-y-2 xl:flex-row xl:space-y-0 xl:space-x-2"
                    >
                      <strong>Service:</strong>
                      <span v-text="ucfirst(review.service_rating)" />
                    </li>
                  </ul>
                </div>
              </div>
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
