<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { NearbyTown } from '@/types/EateryTypes';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/solid';
import { pluralise } from '@/helpers';

defineProps<{ heading: string; towns: NearbyTown[] }>();
</script>

<template>
  <Card>
    <SubHeading>{{ heading }}</SubHeading>

    <div class="mt-2 flex flex-col">
      <div
        v-for="town in towns"
        :key="town.link"
        class="group relative -mx-3 flex flex-col space-y-2 rounded-sm p-3 transition hover:bg-linear-to-br hover:from-primary/30 hover:to-primary-light/30"
      >
        <Link
          :href="town.link"
          prefetch="click"
          class="absolute top-0 left-0 h-full w-full"
        />

        <div class="flex items-center justify-between space-x-2">
          <h3
            class="text-lg font-semibold text-primary-darkest transition group-hover:text-black lg:text-xl"
          >
            {{ town.name }}
          </h3>

          <ArrowRightIcon
            class="size-5 shrink-0 text-primary-dark transition group-hover:translate-x-1 group-hover:text-black"
          />
        </div>

        <ul class="flex flex-wrap gap-2">
          <li
            v-if="town.eateries > 0"
            class="rounded-lg bg-primary/50 px-3 py-1 text-xs font-semibold"
          >
            {{ town.eateries }} {{ pluralise('Eatery', town.eateries) }}
          </li>

          <li
            v-if="town.attractions > 0"
            class="rounded-lg bg-primary-dark/50 px-3 py-1 text-xs font-semibold"
          >
            {{ town.attractions }}
            {{ pluralise('Attraction', town.attractions) }}
          </li>

          <li
            v-if="town.hotels > 0"
            class="rounded-lg bg-secondary/50 px-3 py-1 text-xs font-semibold"
          >
            {{ town.hotels }} {{ pluralise('Hotel', town.hotels) }}
          </li>
        </ul>
      </div>
    </div>
  </Card>
</template>
