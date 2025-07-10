<script setup lang="ts">
import { LondonPageBorough } from '@/types/EateryTypes';
import { pluralise } from '@/helpers';
import StaticMap from '@/Components/Maps/StaticMap.vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';

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
    class="group relative overflow-hidden !rounded-lg"
    no-padding
  >
    <Link
      :href="borough.link"
      class="absolute top-0 left-0 h-full w-full"
      prefetch="click"
    />

    <div class="flex h-full flex-col justify-between">
      <div class="flex flex-col justify-between space-y-4 p-4">
        <h3 class="text-2xl font-semibold md:max-lg:text-2xl lg:text-3xl">
          Eat gluten free in
          <span
            class="text-primary-darkest"
            v-text="borough.name"
          />
        </h3>

        <div class="prose max-w-none flex-1 lg:prose-lg">
          <div
            class="float-right mb-4 ml-4 hidden aspect-square w-full flex-shrink-0 xs:inline-block xs:max-w-[120px] sm:max-w-[170px]"
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

          {{ borough.description }}
        </div>
      </div>

      <div
        class="border border-primary-lightest bg-gradient-to-b from-primary-lightest/50 to-primary-light/50 p-4 transition group-hover:from-primary-lightest/70 group-hover:to-primary-light/70"
      >
        <p class="prose prose-lg max-w-none font-semibold">
          Find {{ borough.locations }}
          {{ pluralise('place', borough.locations) }} to eat in
          <strong
            class="text-primary-darkest"
            v-text="borough.name"
          />
          across areas including
          <span v-html="formattedAreas(borough.top_areas)" />.
        </p>
      </div>
    </div>
  </Card>
</template>
