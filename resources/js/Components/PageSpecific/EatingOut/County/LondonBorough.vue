<script setup lang="ts">
import { LondonPageBorough } from '@/types/EateryTypes';
import { pluralise } from '@/helpers';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import useJourneyTracking from '@/composables/useJourneyTracking';

defineProps<{ borough: LondonPageBorough }>();

const formattedAreas = (areas: LondonPageBorough['top_areas']): string => {
  if (areas.length === 1) {
    return `<strong class="text-primary-darkest">${areas[0]}</strong>`;
  }

  if (areas.length === 2) {
    return `<strong class="text-primary-darkest">${areas[0]}</strong> and <strong class="text-primary-darkest">${areas[1]}</strong>`;
  }

  const lastArea = areas.pop();

  return `<strong class="text-primary-darkest">${areas.join('</strong>, <strong class="text-primary-darkest">')}</strong> and <strong class="text-primary-darkest">${lastArea}</strong>`;
};
</script>

<template>
  <Card
    ref="card"
    class="relative flex flex-col space-y-3 transition hover:bg-linear-to-br hover:from-primary/30 hover:to-primary-light/30"
  >
    <Link
      :href="borough.link"
      prefetch="click"
      class="absolute top-0 left-0 h-full w-full"
      :on-before="
        () =>
          useJourneyTracking().logEvent(
            'clicked',
            'CountyTown/LondonBoroughLink',
            {
              borough: borough.name,
            },
          )
      "
    />
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold md:max-lg:text-xl lg:text-2xl">
        {{ borough.name }}
      </h3>

      <ul class="flex space-x-4">
        <li class="rounded-lg bg-primary px-4 py-1 text-sm font-semibold">
          {{ borough.area_count }} {{ pluralise('Area', borough.area_count) }}
        </li>

        <li
          v-if="borough.eateries > 0"
          class="rounded-lg bg-primary/50 px-4 py-1 text-sm font-semibold"
        >
          {{ borough.eateries }} {{ pluralise('Eatery', borough.eateries) }}
        </li>

        <li
          v-if="borough.attractions > 0"
          class="rounded-lg bg-primary-dark/50 px-4 py-1 text-sm font-semibold"
        >
          {{ borough.attractions }}
          {{ pluralise('Attraction', borough.attractions) }}
        </li>

        <li
          v-if="borough.hotels > 0"
          class="rounded-lg bg-secondary/50 px-4 py-1 text-sm font-semibold"
        >
          {{ borough.hotels }} {{ pluralise('Hotel', borough.hotels) }}
        </li>
      </ul>
    </div>
    <p>
      Including popular areas such as
      <span v-html="formattedAreas(borough.top_areas)" />
    </p>
  </Card>
</template>
