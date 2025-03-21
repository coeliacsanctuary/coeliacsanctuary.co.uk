<script lang="ts" setup>
import { TownEatery } from '@/types/EateryTypes';
import StarRating from '@/Components/StarRating.vue';
import { Link } from '@inertiajs/vue3';
import { pluralise } from '@/helpers';

withDefaults(
  defineProps<{
    name: string;
    link: string;
    reviews: TownEatery['reviews'];
    isBranch?: boolean;
  }>(),
  { isBranch: false },
);
</script>

<template>
  <div class="mt-2 flex w-full flex-col space-y-3">
    <div
      v-if="reviews.number > 0"
      class="flex items-center justify-between sm:flex-col-reverse sm:items-start"
    >
      <span class="flex-1 sm:mt-2 md:text-lg">
        Rated <strong>{{ reviews.average }} stars</strong> from
        <strong>
          {{ reviews.number }} {{ pluralise('review', reviews.number) }}
        </strong>
        {{ isBranch ? 'across all branches' : '' }}
      </span>

      <StarRating
        :rating="reviews.average"
        show-all
      />
    </div>

    <div
      class="rounded-sm bg-linear-to-br from-primary/30 to-primary-light/30 text-center transition duration-500 hover:from-primary/50 hover:to-primary-light/50 md:text-lg"
    >
      <Link
        :href="link"
        class="block p-2"
        prefetch
      >
        Read more about <strong>{{ name }}</strong
        >, {{ reviews.number > 0 ? ' read experiences from other people' : '' }}
        and leave your review.
      </Link>
    </div>
  </div>
</template>
