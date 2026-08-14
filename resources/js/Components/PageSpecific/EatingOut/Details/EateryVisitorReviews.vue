<script lang="ts" setup>
import {
  DetailedEatery,
  EateryReview as EateryReviewType,
} from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import {
  computed,
  ComputedRef,
  onMounted,
  ref,
  useTemplateRef,
  watch,
} from 'vue';
import RatingsBreakdown from '@/Components/PageSpecific/Shared/RatingsBreakdown.vue';
import FormCheckbox from '@/Components/Forms/FormCheckbox.vue';
import Modal from '@/Components/Overlays/Modal.vue';
import EateryAddReview from '@/Components/PageSpecific/EatingOut/Details/Reviews/EateryAddReview.vue';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import { router } from '@inertiajs/vue3';
import EateryReview from '@/Components/PageSpecific/EatingOut/Details/Reviews/EateryReview.vue';
import SubHeading from '@/Components/SubHeading.vue';
import CoeliacButton from '@/Components/CoeliacButton.vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';
import { pluralise } from '@/helpers';
import useJourneyTracking from '@/composables/useJourneyTracking';

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

const hasNoReviews = computed(() => reviews.value.length === 0);

const clearFilters = (): void => {
  reviewFilter.value = undefined;
  hideReviewsWithoutBody.value = false;
};

const displayAddReviewModal = ref(false);

const openAddReview = (): void => {
  displayAddReviewModal.value = true;
};

defineExpose({ openAddReview });

onMounted(() => {
  if (
    typeof window !== 'undefined' &&
    window.location.hash === '#leave-review'
  ) {
    displayAddReviewModal.value = true;
  }
});

watch(showAllReviews, (newValue) => {
  router.reload({
    data: { 'show-all-reviews': newValue },
    only: ['eatery'],
    replace: true,
  });
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryDetails/VisitorReviews',
  {
    eateryId: props.eatery.id,
    branchId: props.eatery.branch?.id,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="flex flex-col space-y-4"
  >
    <SubHeading class="pb-4">
      Visitor reviews from {{ eateryName() }}
    </SubHeading>

    <div class="mx-auto w-full xl:grid xl:grid-cols-10 xl:gap-x-8">
      <RatingsBreakdown
        v-if="!hasNoReviews"
        class="xl:col-span-3"
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

      <div
        :class="hasNoReviews ? 'xl:col-span-10' : 'mt-8 xl:col-span-7 xl:mt-0'"
      >
        <div class="flow-root">
          <div
            v-if="reviews.length > 0 || eatery.branch"
            class="mb-6 flex w-auto flex-col justify-between space-y-4 rounded-sm bg-primary-light/50 px-3 py-2 sm:flex-row sm:space-y-0 sm:space-x-16"
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

          <div
            v-if="filteredReviews.length > 0"
            class="-my-6 divide-y divide-gray-200"
          >
            <EateryReview
              v-for="review in filteredReviews"
              :key="review.id"
              :review="review"
              :eatery-name="eateryName()"
              :showing-all-reviews="!eatery.branch || showAllReviews"
            />
          </div>

          <div
            v-else
            class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-sm bg-primary-lightest/60 p-6 text-center"
          >
            <ChatBubbleLeftRightIcon class="size-12 text-primary" />

            <template v-if="hasNoReviews">
              <p class="text-lg font-semibold">No reviews yet</p>

              <p class="max-w-md text-sm text-grey-dark">
                Nobody has reviewed {{ eateryName() }} yet &mdash; if you've
                been, let other people know what the gluten free options were
                like!
              </p>

              <CoeliacButton
                v-if="!eatery.closed_down"
                as="button"
                type="button"
                theme="light"
                size="lg"
                bold
                label="Be the first to review"
                @click="displayAddReviewModal = true"
              />
            </template>

            <template v-else>
              <p class="text-lg font-semibold">No reviews match your filters</p>

              <p class="max-w-md text-sm text-grey-dark">
                {{ eateryName() }} has
                {{ reviews.length }}
                {{ pluralise('review', reviews.length) }}, but none of them
                match what you're filtering on.
              </p>

              <CoeliacButton
                as="button"
                type="button"
                theme="light"
                size="lg"
                bold
                label="Show all reviews"
                @click="clearFilters()"
              />
            </template>
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
