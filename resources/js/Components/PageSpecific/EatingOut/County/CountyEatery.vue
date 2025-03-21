<script lang="ts" setup>
import { CountyEatery as CountyEateryType } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';

defineProps<{ eatery: CountyEateryType }>();
</script>

<template>
  <Card
    class="flex flex-col space-y-2 bg-linear-to-br from-primary/50 to-primary-light/50"
  >
    <Link
      :href="eatery.link"
      prefetch
    >
      <h3 class="text-lg font-semibold md:max-md:text-xl lg:text-2xl">
        {{ eatery.name }}
      </h3>
    </Link>

    <div class="flex justify-between">
      <Link
        v-if="eatery.town.name !== 'Nationwide'"
        :href="eatery.town.link"
        class="font-semibold text-primary-dark transition hover:text-grey md:text-lg"
      >
        {{ eatery.town.name }}
      </Link>

      <div class="flex flex-col space-x-0.5">
        <StarRating
          :rating="eatery.rating"
          size="w-5 h-5"
          align="start"
        />
        <p class="font-semibold text-grey-dark">
          {{ eatery.rating.toFixed(1) }} from {{ eatery.rating_count }} votes
        </p>
      </div>
    </div>

    <p
      class="prose max-w-none flex-1 md:prose-lg"
      v-html="eatery.info"
    />

    <div class="flex flex-col space-y-1">
      <p
        v-if="eatery.town.name !== 'Nationwide'"
        class="text-xs text-grey-dark md:text-sm"
        v-html="eatery.address"
      />
      <Link
        v-if="eatery.town.name !== 'Nationwide'"
        :href="eatery.town.link"
        class="text-xs font-semibold text-grey-darker transition hover:text-grey-darkest md:text-sm"
      >
        View all places in {{ eatery.town.name }}
      </Link>
    </div>
  </Card>
</template>
