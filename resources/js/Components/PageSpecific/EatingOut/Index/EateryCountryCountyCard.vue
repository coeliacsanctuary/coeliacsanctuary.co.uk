<script setup lang="ts">
import useJourneyTracking from '@/composables/useJourneyTracking';
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { EateryCountryList } from '@/types/EateryTypes';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{ county: EateryCountryList; country: string; top?: boolean }>(),
  {
    top: false,
  },
);

const columns = computed(() => {
  const checks: boolean[] = [
    props.county.eateries > 0,
    props.county.attractions > 0,
    props.county.hotels > 0,
    props.county.branches > 0,
  ];

  switch (checks.filter((value) => value).length) {
    case 4:
      return 'grid-cols-2 sm:max-xmd:grid-cols-4';
    case 3:
      return 'grid-cols-3';
    case 2:
      return 'grid-cols-2';
    case 1:
    default:
      return 'grid-cols-1';
  }
});

const colSpan = computed(() => {
  const checks: boolean[] = [
    props.county.eateries > 0,
    props.county.attractions > 0,
    props.county.hotels > 0,
    props.county.branches > 0,
  ];

  switch (checks.filter((value) => value).length) {
    case 4:
      return 'col-span-2 sm:max-xmd:col-span-4';
    default:
      return 'col-span-full';
  }
});
</script>

<template>
  <Card
    class="group overflow-hidden !rounded-lg"
    no-padding
  >
    <Link
      :href="'/wheretoeat/' + county.slug"
      class="z-10 flex h-full flex-col justify-between"
      prefetch="click"
      :on-before="
        () =>
          useJourneyTracking().logEvent(
            'clicked',
            'EateryCountryCard/CountyLink',
            { country: country, county: county.name },
          )
      "
    >
      <div
        class="flex w-full items-center justify-between bg-primary-light/80 p-2 shadow"
      >
        <h4
          class="text-md font-semibold xs:text-lg lg:text-xl xl:text-2xl"
          v-text="county.name"
        />

        <ArrowRightIcon
          class="size-4 group-hover:animate-ping lg:size-5 xl:size-6"
        />
      </div>

      <div
        class="relative flex aspect-[1200/630] flex-col justify-end overflow-hidden transition group-hover:bg-gradient-to-b group-hover:from-transparent group-hover:to-black/10"
      >
        <div
          v-if="county.image"
          class="absolute top-0 left-0 h-full w-full opacity-20 transition group-hover:opacity-80"
        >
          <img
            :src="county.image"
            alt=""
            class="h-full w-full object-cover object-center"
          />
        </div>

        <div class="flex-1" />

        <div
          class="z-10 m-3 grid gap-2"
          :class="columns"
        >
          <div
            v-if="county.eateries > 0"
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base sm:text-lg"
          >
            {{ county.eateries }}
            {{ pluralise('eatery', county.eateries) }}
          </div>
          <div
            v-if="county.branches > 0"
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base sm:text-lg"
          >
            {{ county.branches }}
            {{ pluralise('chain', county.branches) }}
          </div>

          <div
            v-if="county.attractions > 0"
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base sm:text-lg"
          >
            {{ county.attractions }}
            {{ pluralise('attraction', county.attractions) }}
          </div>

          <div
            v-if="county.hotels > 0"
            class="rounded bg-secondary/70 px-2 py-1 text-sm font-semibold xs:text-base sm:text-lg"
          >
            {{ county.hotels }} {{ pluralise('hotel', county.hotels) }}
          </div>

          <div
            v-if="top"
            class="rounded bg-primary/50 px-2 py-1 text-sm font-semibold xs:text-base sm:text-lg"
            :class="colSpan"
          >
            Average rating {{ county.avg_rating.toFixed(1) }} from
            {{ county.review_count }} ratings
          </div>
        </div>
      </div>
    </Link>
  </Card>
</template>
