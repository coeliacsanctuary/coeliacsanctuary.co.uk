<script lang="ts" setup>
import { DetailedEatery, NearbyEatery } from '@/types/EateryTypes';
import EateryHeading from '@/Components/PageSpecific/EatingOut/Details/EateryHeading.vue';
import EateryDescription from '@/Components/PageSpecific/EatingOut/Details/EateryDescription.vue';
import EateryLocation from '@/Components/PageSpecific/EatingOut/Details/EateryLocation.vue';
import EateryAdminReview from '@/Components/PageSpecific/EatingOut/Details/EateryAdminReview.vue';
import EateryVisitorPhotos from '@/Components/PageSpecific/EatingOut/Details/EateryVisitorPhotos.vue';
import EateryVisitorReviews from '@/Components/PageSpecific/EatingOut/Details/EateryVisitorReviews.vue';
import EateryFeedbackLinks from '@/Components/PageSpecific/EatingOut/Details/EateryFeedbackLinks.vue';
import { Ref, ref } from 'vue';
import { formatDate } from '@/helpers';
import EateryBranchList from '@/Components/PageSpecific/EatingOut/Details/EateryBranchList.vue';
import GoogleAd from '@/Components/GoogleAd.vue';
import EateryAiOverview from '@/Components/PageSpecific/EatingOut/Details/EateryAiOverview.vue';
import NearbyEateries from '@/Components/PageSpecific/EatingOut/Details/NearbyEateries.vue';

const props = defineProps<{
  eatery: DetailedEatery;
  previous: string;
  name: string;
  nearbyEateries?: NearbyEatery[];
}>();

const reviewsElem: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;

const goToReview = () => {
  reviewsElem.value.scrollIntoView({
    behavior: 'smooth',
  });
};

const eateryName = (): string => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};

const shouldShowAiOverview = ref(true);
</script>

<template>
  <div class="flex flex-col space-y-3 lg:max-xl:space-y-4 xl:space-y-5">
    <div
      v-if="eatery.closed_down"
      class="bg-red px-3 py-1 text-lg font-semibold text-white"
    >
      This eatery was unfortunately marked as closed down on
      {{ formatDate(eatery.last_updated) }}.
    </div>

    <EateryHeading
      :eatery="eatery"
      :previous="previous"
      :name="name"
    />

    <EateryFeedbackLinks
      v-if="!eatery.closed_down"
      :eatery="eatery"
      @go-to-review="goToReview()"
    />

    <div
      class="flex flex-col space-y-3 md:flex-row md:space-y-0 md:max-lg:space-x-3 lg:max-xl:space-x-4 xl:space-x-5"
    >
      <EateryDescription
        class="md:flex-1"
        :eatery="eatery"
      />

      <EateryBranchList
        v-if="
          eatery.is_nationwide &&
          eatery.nationwide_branches &&
          eatery.nationwide_branches?.length !== 0
        "
        class="md:w-1/3 md:shrink-0 md:grow-0 xl:w-1/4"
        :eatery="eatery"
      />
    </div>

    <EateryLocation
      v-if="eatery.county.id !== 1 || eatery.branch"
      :eatery="eatery"
    />

    <GoogleAd
      :key="$page.url"
      code="5284484376"
    />

    <EateryAiOverview
      v-if="eatery.qualifies_for_ai && shouldShowAiOverview"
      :eatery-id="eatery.id"
      :branch-id="eatery.branch?.id"
      :eatery-name="eateryName()"
      @on-error="shouldShowAiOverview = false"
    />

    <EateryAdminReview
      v-if="eatery.reviews.admin_review"
      :eatery-name="eateryName()"
      :review="eatery.reviews.admin_review"
    />

    <EateryVisitorPhotos
      v-if="eatery.reviews?.images?.length > 0"
      :eatery="eatery"
    />

    <div ref="reviewsElem">
      <EateryVisitorReviews :eatery="eatery" />
    </div>

    <NearbyEateries
      :eatery-name="eateryName()"
      :nearby-eateries="nearbyEateries"
    />
  </div>
</template>
