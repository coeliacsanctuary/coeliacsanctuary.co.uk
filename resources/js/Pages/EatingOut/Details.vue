<script lang="ts" setup>
import {
  DetailedEatery,
  EateryBranchesCollection,
  NearbyEatery,
} from '@/types/EateryTypes';
import SidebarLayout from '@/Components/SidebarLayout.vue';
import ClosedDownBanner from '@/Components/PageSpecific/EatingOut/Details/ClosedDownBanner.vue';
import EateryHeading from '@/Components/PageSpecific/EatingOut/Details/EateryHeading.vue';
import EateryDescription from '@/Components/PageSpecific/EatingOut/Details/EateryDescription.vue';
import EateryKeyInfo from '@/Components/PageSpecific/EatingOut/Details/EateryKeyInfo.vue';
import EateryRatingSummary from '@/Components/PageSpecific/EatingOut/Details/EateryRatingSummary.vue';
import EateryVisitorPhotos from '@/Components/PageSpecific/EatingOut/Details/EateryVisitorPhotos.vue';
import EateryVisitorReviews from '@/Components/PageSpecific/EatingOut/Details/EateryVisitorReviews.vue';
import EateryFeedbackLinks from '@/Components/PageSpecific/EatingOut/Details/EateryFeedbackLinks.vue';
import EateryBranchList from '@/Components/PageSpecific/EatingOut/Details/EateryBranchList.vue';
import EateryAiOverview from '@/Components/PageSpecific/EatingOut/Details/EateryAiOverview.vue';
import NearbyEateries from '@/Components/PageSpecific/EatingOut/Details/NearbyEateries.vue';
import { computed, Ref, ref, useTemplateRef } from 'vue';

const props = defineProps<{
  eatery: DetailedEatery;
  previous: string;
  name: string;
  nearbyEateries?: NearbyEatery[];
  branches?: EateryBranchesCollection;
}>();

const reviewsElem: Ref<HTMLDivElement> = ref() as Ref<HTMLDivElement>;
const reviewsSection = useTemplateRef<{ openAddReview: () => void }>(
  'reviewsSection',
);

const goToReview = () => {
  reviewsElem.value.scrollIntoView({
    behavior: 'smooth',
  });

  reviewsSection.value?.openAddReview();
};

const eateryName = (): string => {
  if (
    props.eatery.branch &&
    props.eatery.branch.name &&
    props.eatery.name !== props.eatery.branch.name
  ) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};

const hasLocation = computed(() => {
  if (props.eatery.branch) {
    return true;
  }

  return props.eatery.county.id !== 1;
});

// The branches themselves are deferred, so the gate can't depend on them.
const hasBranchList = computed(
  () => props.eatery.is_nationwide && !props.eatery.branch,
);

const shouldShowAiOverview = ref(true);
</script>

<template>
  <SidebarLayout
    class="mt-3"
    interleaved
  >
    <template #sidebar>
      <EateryKeyInfo
        v-if="hasLocation"
        class="order-4 xmd:order-2"
        :eatery="eatery"
      />

      <EateryBranchList
        v-else-if="hasBranchList"
        class="order-4 xmd:order-2"
        :eatery="eatery"
        :branches="branches"
      />

      <EateryRatingSummary
        class="max-xmd:hidden xmd:order-1"
        :eatery="eatery"
        @go-to-review="goToReview()"
      />

      <EateryFeedbackLinks
        v-if="!eatery.closed_down"
        class="order-3"
        :eatery="eatery"
        @go-to-review="goToReview()"
      />

      <NearbyEateries
        v-if="hasLocation"
        class="order-8 xmd:order-4"
        :eatery-name="eateryName()"
        :nearby-eateries="nearbyEateries"
      />
    </template>

    <ClosedDownBanner
      v-if="eatery.closed_down"
      class="order-first"
      :last-updated="eatery.last_updated"
    />

    <EateryHeading
      class="order-1"
      :eatery="eatery"
      :previous="previous"
      :name="name"
    />

    <EateryDescription
      class="order-2"
      :eatery="eatery"
    />

    <EateryAiOverview
      v-if="eatery.qualifies_for_ai && shouldShowAiOverview"
      class="order-5"
      :eatery-id="eatery.id"
      :branch-id="eatery.branch?.id"
      :eatery-name="eateryName()"
      @on-error="shouldShowAiOverview = false"
    />

    <EateryVisitorPhotos
      v-if="eatery.reviews?.images?.length"
      class="order-6"
      :eatery="eatery"
    />

    <div
      ref="reviewsElem"
      class="order-7"
    >
      <EateryVisitorReviews
        ref="reviewsSection"
        :eatery="eatery"
      />
    </div>
  </SidebarLayout>
</template>
