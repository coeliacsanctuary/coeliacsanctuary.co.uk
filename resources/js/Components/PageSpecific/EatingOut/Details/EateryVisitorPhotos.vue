<script lang="ts" setup>
import { DetailedEatery, ReviewImage } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import ReviewImageGallery from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageGallery.vue';
import SubHeading from '@/Components/SubHeading.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

const eateryName = (): string => {
  if (props.eatery.branch && props.eatery.branch.name) {
    return `${props.eatery.branch.name} - ${props.eatery.name}`;
  }

  return props.eatery.name;
};

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryDetails/VisitorPhotos',
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
    <SubHeading>Photos from {{ eateryName() }}</SubHeading>

    <p class="prose max-w-none md:prose-lg">
      Here are some photos taken at <strong>{{ eateryName() }}</strong> that
      other visitors have submitted!
    </p>

    <ReviewImageGallery
      :images="eatery.reviews.images as ReviewImage[]"
      :eatery-name="eateryName()"
    />
  </Card>
</template>
