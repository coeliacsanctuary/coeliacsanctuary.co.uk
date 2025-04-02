<script setup lang="ts">
import CoeliacButton from '@/Components/CoeliacButton.vue';
import StarRating from '@/Components/StarRating.vue';
import { StarRating as StarRatingType } from '@/types/EateryTypes';
import { pluralise } from '@/helpers';

withDefaults(
  defineProps<{
    canAddReview?: boolean;
    average: StarRatingType;
    count: number;
    breakdown: {
      rating: StarRatingType;
      count: number;
    }[];
    filterable?: boolean;
    filteredOn?: StarRatingType;
  }>(),
  { canAddReview: true, filterable: false, filteredOn: undefined },
);

defineEmits(['createReview', 'filter']);
</script>

<template>
  <div class="">
    <div class="mt-3 flex items-center space-x-2">
      <div>
        <div class="flex items-center">
          <StarRating
            :rating="average"
            show-all
          />
        </div>
      </div>
      <p class="text-right text-sm">
        Based on {{ count }} {{ pluralise('review', count) }}
      </p>
    </div>

    <div class="mt-6">
      <dl>
        <div
          v-for="item in breakdown"
          :key="item.rating"
          class="flex items-center text-sm py-2 transition rounded-lg"
          :class="{
            'cursor-pointer hover:bg-primary-light/20 px-2 -mx-2 ': filterable,
            'bg-primary-light/50 hover:bg-primary-light/50':
              filteredOn === item.rating,
          }"
          @click="filterable ? $emit('filter', item.rating) : undefined"
        >
          <dt class="flex flex-1 items-center">
            <p class="w-3 font-semibold">
              {{ item.rating }}
            </p>

            <div class="ml-1 flex flex-1 items-center">
              <StarRating
                :rating="1"
                size="w-4 h-4"
              />

              <div class="relative ml-3 flex-1">
                <div
                  class="h-3 rounded-full border border-gray-200 bg-gray-100"
                />

                <div
                  v-if="item.count > 0"
                  class="absolute inset-y-0 rounded-full border border-secondary bg-secondary"
                  :style="{
                    width: `calc(${item.count} / ${count} * 100%)`,
                  }"
                />
              </div>
            </div>
          </dt>

          <dd
            v-if="count > 0"
            class="ml-3 w-15 text-right text-sm tabular-nums text-gray-900"
          >
            {{ item.count }} ({{ Math.round((item.count / count) * 100) }}%)
          </dd>
        </div>
      </dl>
    </div>

    <div
      v-if="canAddReview"
      class="mt-10 space-y-3"
    >
      <h3 class="text-lg font-semibold">Share your thoughts</h3>

      <p class="text-sm">
        <slot />
      </p>

      <CoeliacButton
        label="Write a review"
        theme="light"
        size="xl"
        as="button"
        type="button"
        @click="$emit('createReview')"
      />
    </div>
  </div>
</template>
