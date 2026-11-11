<script lang="ts" setup>
import { CountyEatery as CountyEateryType } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';
import { useTemplateRef } from 'vue';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { pluralise } from '@/helpers';

const props = withDefaults(
  defineProps<{ eatery: CountyEateryType; minimal?: boolean }>(),
  {
    minimal: false,
  },
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'CountyEatery',
  {
    eateryId: props.eatery.id,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="flex flex-col space-y-2 bg-linear-to-br from-primary/50 to-primary-light/50"
  >
    <div class="flex justify-between space-x-1">
      <Link
        :href="eatery.link"
        prefetch
      >
        <h3 class="text-lg font-semibold md:max-md:text-xl lg:text-2xl">
          {{ eatery.name }}
        </h3>
      </Link>

      <div class="flex flex-col space-x-0.5">
        <StarRating
          :rating="eatery.rating"
          size="w-5 h-5"
          align="start"
          show-all
        />
        <p class="text-sm font-semibold text-grey-dark">
          {{ parseInt(eatery.rating as string, 10).toFixed(1) }} from
          {{ eatery.rating_count }}
          {{ pluralise('rating', eatery.rating_count) }}
        </p>
      </div>
    </div>

    <div
      v-if="!minimal"
      class="flex justify-between"
    >
      <Link
        v-if="eatery.town.name !== 'Nationwide'"
        :href="eatery.town.link"
        class="font-semibold text-primary-dark transition hover:text-grey md:text-lg"
      >
        {{ eatery.town.name }}
      </Link>
    </div>

    <p
      v-if="!minimal"
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
