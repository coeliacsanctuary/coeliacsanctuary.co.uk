<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { EaterySimpleReviewResource } from '@/types/EateryTypes';
import StarRating from '@/Components/StarRating.vue';

defineProps<{ reviews: EaterySimpleReviewResource[] }>();
</script>

<template>
  <Card class="space-y-4 pb-10">
    <h3 class="text-center text-2xl font-semibold text-primary-dark">
      Latest Ratings
    </h3>

    <p class="prose">
      These are the latest ratings people have left on locations in our
      <Link href="/eating-out">Eating Out</Link> guide.
    </p>

    <ul class="divide-y divide-primary-dark/80">
      <li
        v-for="review in reviews"
        :key="review.eatery.link"
        class="flex flex-col p-2"
      >
        <div class="flex items-center justify-between">
          <Link
            :href="review.eatery.link"
            class="text-lg font-semibold text-primary-dark hover:text-black hover:underline"
            prefetch
          >
            {{ review.eatery.name }}
          </Link>

          <div>
            <StarRating :rating="review.rating" />
          </div>
        </div>

        <Link
          :href="review.eatery.location.link"
          class="hover:underline"
        >
          {{ review.eatery.location.name }}
        </Link>
        <span
          class="mt-4! block text-sm italic"
          v-text="review.created_at"
        />
      </li>
    </ul>
  </Card>
</template>
