<script setup lang="ts">
import useJourneyTracking from '@/composables/useJourneyTracking';
import { pluralise } from '@/helpers';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';
import { EateryCountryList } from '@/types/EateryTypes';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{ county: EateryCountryList; country: string; top?: boolean }>(),
  {
    top: false,
  },
);

const counts = computed(() =>
  [
    { key: 'eateries', label: 'eatery', value: props.county.eateries },
    { key: 'branches', label: 'chain', value: props.county.branches },
    {
      key: 'attractions',
      label: 'attraction',
      value: props.county.attractions,
    },
    { key: 'hotels', label: 'hotel', value: props.county.hotels },
  ].filter((count) => count.value > 0),
);
</script>

<template>
  <Card
    class="group @container overflow-hidden !rounded-lg"
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
          class="text-base font-semibold @sm:text-lg @md:text-xl"
          v-text="county.name"
        />

        <ArrowRightIcon
          class="size-4 shrink-0 group-hover:animate-ping @md:size-5"
        />
      </div>

      <div
        class="relative flex min-h-40 grow items-end overflow-hidden p-3 transition group-hover:bg-gradient-to-b group-hover:from-transparent group-hover:to-black/10"
      >
        <div
          v-if="county.image"
          class="absolute top-0 left-0 h-full w-full opacity-20 transition group-hover:opacity-80"
        >
          <img
            :src="county.image"
            alt=""
            loading="lazy"
            class="h-full w-full object-cover object-center"
          />
        </div>

        <p
          v-if="top"
          class="z-10 flex w-fit items-center gap-1 rounded bg-primary/50 px-2 py-1 text-sm font-semibold @md:text-base"
        >
          <StarIcon class="size-4 shrink-0" />
          {{ county.avg_rating.toFixed(1) }} ({{ county.review_count }})
        </p>

        <ul
          class="z-10 ml-auto flex w-fit max-w-1/2 flex-col gap-1.5 text-sm font-semibold @md:text-base"
        >
          <li
            v-for="count in counts"
            :key="count.key"
            class="w-full rounded bg-secondary/70 px-2 py-1"
          >
            {{ count.value }} {{ pluralise(count.label, count.value) }}
          </li>
        </ul>
      </div>
    </Link>
  </Card>
</template>
