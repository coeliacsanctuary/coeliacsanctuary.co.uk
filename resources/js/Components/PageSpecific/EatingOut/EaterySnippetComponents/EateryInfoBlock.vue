<script lang="ts" setup>
import EateryRestaurants from '@/Components/PageSpecific/EatingOut/EaterySnippetComponents/EateryRestaurants.vue';
import { TownEatery } from '@/types/EateryTypes';

defineProps<{
  restaurants: TownEatery['restaurants'];
  info: string;
  isNotNationwide: boolean;
  distance?: string;
  address: string;
  phone?: string;
}>();
</script>

<template>
  <div class="flex w-full">
    <div class="flex w-full flex-col space-y-2 md:space-y-4">
      <EateryRestaurants
        v-if="restaurants.length"
        :restaurants="restaurants"
      />

      <p
        v-if="restaurants.length === 0"
        class="prose max-w-none md:prose-lg"
        v-html="info"
      />

      <div
        v-if="isNotNationwide"
        class="mt-2 flex flex-col font-semibold text-grey-darkest"
      >
        <span v-if="distance">Around {{ distance }} miles away...</span>

        <span
          class="block md:text-lg"
          v-html="address"
        />
        <span
          v-if="phone"
          class="md:text-lg"
          v-text="phone"
        />
      </div>
    </div>
  </div>
</template>
