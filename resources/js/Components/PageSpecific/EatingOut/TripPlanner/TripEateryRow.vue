<script setup lang="ts">
import { TripEatery } from '@/types/TripPlannerTypes';
import {
  formatDuration,
  metersToMiles,
} from '@/support/eating-out/trip-planner/format';
import StarRating from '@/Components/StarRating.vue';
import { StarRating as StarRatingValue } from '@/types/EateryTypes';

defineProps<{ eatery: TripEatery; highlighted?: boolean }>();

defineEmits(['select', 'hover']);
</script>

<template>
  <button
    type="button"
    class="flex w-full cursor-pointer items-start gap-3 border-b border-grey-off p-2 text-left transition hover:bg-primary-lightest"
    :class="{ 'bg-primary-lightest': highlighted }"
    :data-eatery-key="eatery.key"
    @click="$emit('select', eatery)"
    @mouseenter="$emit('hover', eatery)"
    @mouseleave="$emit('hover', undefined)"
  >
    <span
      class="mt-1.5 size-3 shrink-0 rounded-full"
      :style="{ backgroundColor: eatery.color }"
    />

    <span class="flex min-w-0 flex-1 flex-col">
      <span
        class="truncate font-semibold"
        v-text="eatery.name"
      />

      <span
        class="truncate text-sm text-grey-dark"
        v-text="[eatery.chain, eatery.town].filter(Boolean).join(' · ')"
      />

      <span class="text-sm text-grey-dark">
        {{ metersToMiles(eatery.distanceFromRoute) }} mi off &middot;
        {{ formatDuration(eatery.durationAlongRoute) }} in
      </span>
    </span>

    <span
      v-if="eatery.rating"
      class="flex shrink-0 items-center gap-1 text-sm"
    >
      <StarRating
        :rating="eatery.rating as StarRatingValue"
        show-all
        size="w-4 h-4"
      />
      {{ eatery.rating }}
      <span class="text-grey-dark">({{ eatery.reviewsCount }})</span>
    </span>
  </button>
</template>
