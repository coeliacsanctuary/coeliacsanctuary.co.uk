<script lang="ts" setup>
import { CountyPageTown } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import { pluralise } from '@/helpers';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{ town: CountyPageTown }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CountyTown',
  {
    town: props.town.name,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="relative flex flex-row items-center justify-between transition hover:bg-linear-to-br hover:from-primary/30 hover:to-primary-light/30"
  >
    <Link
      :href="town.link"
      prefetch="click"
      class="absolute top-0 left-0 h-full w-full"
      :on-before="
        () =>
          useJourneyTracking().logEvent('clicked', 'CountyTown/TownLink', {
            town: town.name,
          })
      "
    >
    </Link>

    <h3 class="text-lg font-semibold md:max-lg:text-xl lg:text-2xl">
      {{ town.name }}
    </h3>

    <ul class="flex space-x-4">
      <li
        v-if="town.eateries > 0"
        class="rounded-lg bg-primary/50 px-4 py-1 text-sm font-semibold"
      >
        {{ town.eateries }} {{ pluralise('Eatery', town.eateries) }}
      </li>

      <li
        v-if="town.attractions > 0"
        class="rounded-lg bg-primary-dark/50 px-4 py-1 text-sm font-semibold"
      >
        {{ town.attractions }} {{ pluralise('Attraction', town.attractions) }}
      </li>

      <li
        v-if="town.hotels > 0"
        class="rounded-lg bg-secondary/50 px-4 py-1 text-sm font-semibold"
      >
        {{ town.hotels }} {{ pluralise('Hotel', town.hotels) }}
      </li>
    </ul>
  </Card>
</template>
