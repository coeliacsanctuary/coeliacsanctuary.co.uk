<script lang="ts" setup>
import { DetailedEatery, ReviewImage } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import ReviewImageGallery from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageGallery.vue';

defineProps<{
  eatery: DetailedEatery;
}>();
</script>

<template>
  <Card class="space-y-2 lg:space-y-4 lg:rounded-lg lg:p-8">
    <p class="prose max-w-none lg:max-xl:prose-lg xl:prose-xl">
      Here are some photos taken at <strong>{{ eatery.name }}</strong> that
      other visitors have submitted!
    </p>

    <ReviewImageGallery
      :images="eatery.reviews.images as ReviewImage[]"
      :eatery-name="eatery.name"
    />
  </Card>
</template>
