<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleHomeResource } from '@/types/EateryTypes';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{ eateries: EaterySimpleHomeResource[] }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'Home/LatestEateries',
);
</script>

<template>
  <Card
    ref="card"
    class="space-y-4 overflow-hidden"
  >
    <SubHeading
      as="h2"
      text-size="xs"
    >
      Latest Eateries
    </SubHeading>

    <p class="prose">
      Here are the latest places that have been added to my comprehensive
      <Link href="/eating-out">Eating Out</Link> guide.
    </p>

    <ul class="-mx-2 divide-y divide-grey-off-light">
      <li
        v-for="eatery in eateries"
        :key="eatery.link"
      >
        <Link
          :href="eatery.link"
          class="group flex flex-col rounded-sm px-2 py-3 transition hover:bg-primary-lightest/60"
          prefetch
        >
          <span
            class="font-semibold text-primary-dark transition group-hover:text-black"
            v-text="eatery.name"
          />

          <span
            class="text-sm text-grey-darker"
            v-text="eatery.location.name"
          />

          <span
            class="mt-1 text-xs text-grey-dark italic"
            v-text="eatery.created_at"
          />
        </Link>
      </li>
    </ul>

    <Link
      href="/eating-out"
      class="-mx-4 -mb-4 mt-4! flex items-center justify-center space-x-2 bg-primary-lightest/60 p-4 font-semibold transition hover:bg-primary-light/60"
    >
      <span>View more places to eat!</span>

      <ArrowRightIcon class="size-4" />
    </Link>
  </Card>
</template>
