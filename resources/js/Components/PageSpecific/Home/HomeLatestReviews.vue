<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleReviewResource } from '@/types/EateryTypes';
import StarRating from '@/Components/StarRating.vue';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

defineProps<{ reviews: EaterySimpleReviewResource[] }>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'Home/LatestReviews',
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
      Latest Ratings
    </SubHeading>

    <p class="prose">
      These are the latest ratings people have left on locations in my
      <Link href="/eating-out">Eating Out</Link> guide.
    </p>

    <ul class="-mx-2 divide-y divide-grey-off-light">
      <li
        v-for="(review, index) in reviews"
        :key="`${review.eatery.link}-${index}`"
      >
        <Link
          :href="review.eatery.link"
          class="group flex flex-col rounded-sm px-2 py-3 transition hover:bg-primary-lightest/60"
          prefetch
        >
          <span class="flex items-center justify-between gap-2">
            <span
              class="font-semibold text-primary-dark transition group-hover:text-black"
              v-text="review.eatery.name"
            />

            <StarRating
              class="shrink-0"
              :rating="review.rating"
            />
          </span>

          <span
            class="text-sm text-grey-darker"
            v-text="review.eatery.location.name"
          />

          <span
            class="mt-1 text-xs text-grey-dark italic"
            v-text="review.created_at"
          />
        </Link>
      </li>
    </ul>

    <Link
      href="/eating-out"
      class="-mx-4 -mb-4 mt-4! flex items-center justify-center space-x-2 bg-primary-lightest/60 p-4 font-semibold transition hover:bg-primary-light/60"
    >
      <span>Explore the Eating Out guide</span>

      <ArrowRightIcon class="size-4" />
    </Link>
  </Card>
</template>
