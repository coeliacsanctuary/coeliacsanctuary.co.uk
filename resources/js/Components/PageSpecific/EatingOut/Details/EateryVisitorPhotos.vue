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
    class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8"
  >
    <SubHeading>Photos from others at {{ eateryName() }}</SubHeading>

    <p class="prose mt-2 max-w-none lg:max-xl:prose-lg xl:prose-xl">
      Here are some photos taken at <strong>{{ eateryName() }}</strong> that
      other visitors have submitted!
    </p>

    <ReviewImageGallery
      :images="eatery.reviews.images as ReviewImage[]"
      :eatery-name="eateryName()"
    />
  </Card>
</template>
