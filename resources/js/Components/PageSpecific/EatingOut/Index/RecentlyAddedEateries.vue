<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleHomeResource } from '@/types/EateryTypes';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{ eateries: EaterySimpleHomeResource[] }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'WhereToEatIndexRecentlyAdded',
);
</script>

<template>
  <Card
    ref="card"
    class="flex flex-col space-y-4"
  >
    <SubHeading
      text-size="small"
      class="pb-2"
    >
      Recently added places
    </SubHeading>

    <p class="prose max-w-none">
      The newest gluten free places to join our eating out guide, added by
      people just like you!
    </p>

    <ul class="divide-y divide-primary-dark/30">
      <li
        v-for="eatery in eateries"
        :key="eatery.link"
        class="flex flex-col py-2 first:pt-0 last:pb-0"
      >
        <Link
          :href="eatery.link"
          class="font-semibold text-primary-dark transition hover:text-black"
          prefetch="click"
          :on-before="
            () =>
              useJourneyTracking().logEvent(
                'clicked',
                'WhereToEatIndexRecentlyAdded/EateryLink',
                { eatery: eatery.name },
              )
          "
        >
          {{ eatery.name }}
        </Link>

        <Link
          :href="eatery.location.link"
          class="text-sm text-grey-dark transition hover:text-grey-darkest"
        >
          {{ eatery.location.name }}
        </Link>

        <span
          class="text-xs text-grey-dark italic"
          v-text="eatery.created_at"
        />
      </li>
    </ul>
  </Card>
</template>
