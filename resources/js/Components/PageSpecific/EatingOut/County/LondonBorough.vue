<script setup lang="ts">
import { LondonPageBorough } from '@/types/EateryTypes';
import { pluralise } from '@/helpers';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

defineProps<{ borough: LondonPageBorough }>();

const formattedAreas = (areas: LondonPageBorough['top_areas']): string => {
  if (areas.length === 1) {
    return `<strong>${areas[0]}</strong>`;
  }

  if (areas.length === 2) {
    return `<strong>${areas[0]}</strong> and <strong>${areas[1]}</strong>`;
  }

  const lastArea = areas.pop();

  return `<strong>${areas.join('</strong>, <strong>')}</strong> and <strong>${lastArea}</strong>`;
};
</script>

<template>
  <Card class="relative">
    <Link
      :href="borough.link"
      class="absolute h-full w-full"
      prefetch="click"
    />

    <div class="flex justify-between space-x-4">
      <div class="flex-1 space-y-4">
        <h3
          class="text-2xl font-semibold md:max-lg:text-2xl lg:text-3xl"
          v-text="borough.name"
        />

        <p
          class="prose prose-lg max-w-none"
          v-text="borough.description"
        />

        <p class="prose prose-xl max-w-none">
          Find {{ borough.locations }}
          {{ pluralise('place', borough.locations) }} to eat in
          <strong v-text="borough.name" /> across areas including
          <span v-html="formattedAreas(borough.top_areas)" />.
        </p>
      </div>

      <div
        class="hidden aspect-square w-full flex-shrink-0 xs:block xs:max-w-[120px] sm:max-w-[170px]"
      >
        <StaticMap
          :lat="borough.latlng.lat"
          :lng="borough.latlng.lng"
          :can-expand="false"
          map-classes="!bg-cover h-full max-h-[120px] sm:max-h-[170px]"
          :additional-params="{
            zoom: '10',
            size: '200x200',
          }"
        />
      </div>
    </div>
  </Card>
</template>
