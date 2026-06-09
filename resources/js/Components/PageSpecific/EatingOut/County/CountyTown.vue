<script lang="ts" setup>
import { CountyPageTown } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import { computed, useTemplateRef } from 'vue';
import { numberToWords } from '@/helpers';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{ town: CountyPageTown }>();

const info = computed((): string => {
  if (
    props.town.eateries === 0 &&
    props.town.attractions === 0 &&
    props.town.hotels === 0
  ) {
    return 'No places found...';
  }

  const { eateries, attractions, hotels } = props.town;

  const snippet: string[] = [];

  if (eateries > 0) {
    snippet.push(
      `<span class="font-semibold">${numberToWords(
        eateries,
      )}</span> gluten free place${eateries > 1 ? 's' : ''} to eat`,
    );
  }

  if (attractions > 0) {
    snippet.push(
      `<span class="font-semibold">${numberToWords(
        attractions,
      )}</span> attraction${
        attractions > 1 ? 's' : ''
      } with gluten free options`,
    );
  }

  if (hotels > 0) {
    snippet.push(
      `<span class="font-semibold">${numberToWords(hotels)}</span> hotel${
        hotels > 1 ? 's' : ''
      } / B&B${hotels > 1 ? 's' : ''} with gluten free options`,
    );
  }

  const last = snippet.length > 1 ? snippet.pop() : null;

  return `We've got ${snippet.join(', ')}${
    last ? ` and ${last}` : ''
  } listed in our eating out guide.`;
});

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
    class="flex flex-col bg-linear-to-br from-primary/50 to-primary-light/50 hover:from-primary/70 hover:to-primary-light/70"
  >
    <Link
      :href="town.link"
      prefetch="click"
      :on-before="
        () =>
          useJourneyTracking().logEvent('clicked', 'CountyTown/TownLink', {
            town: town.name,
          })
      "
    >
      <h3 class="mb-4 text-lg font-semibold md:max-lg:text-xl lg:text-2xl">
        {{ town.name }}
      </h3>

      <p
        class="prose flex-1"
        v-html="info"
      />
    </Link>
  </Card>
</template>
