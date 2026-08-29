<script lang="ts" setup>
import { DetailedEatery } from '@/types/EateryTypes';
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Icon from '@/Components/Icon.vue';
import { computed, useTemplateRef } from 'vue';
import useJourneyTracking from '@/composables/useJourneyTracking';

const props = defineProps<{
  eatery: DetailedEatery;
}>();

/**
 * The town the heading advertises, taken from the branch on a branch page.
 * Nationwide chains have no meaningful town, so they drop the location entirely.
 */
const locationName = computed(() => {
  if (props.eatery.branch) {
    return props.eatery.branch.town.name;
  }

  if (props.eatery.town.name === 'Nationwide') {
    return null;
  }

  return props.eatery.town.name;
});

/** Leads with the brand name rather than the branch name, which is already the h1. */
const heading = computed(() => {
  if (locationName.value) {
    return `Gluten free at ${props.eatery.name} in ${locationName.value}`;
  }

  return `Gluten free at ${props.eatery.name}`;
});

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'EateryDetails/Description',
  {
    eateryId: props.eatery.id,
    branchId: props.eatery.branch?.id,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="flex flex-col space-y-4"
  >
    <template v-if="eatery.restaurants.length">
      <SubHeading>
        Here's some restaurants in {{ eatery.name }} that have gluten free
        options
      </SubHeading>

      <div
        v-for="restaurant in eatery.restaurants"
        :key="restaurant.name"
        class="flex flex-col space-y-1"
      >
        <h4
          v-if="restaurant.name"
          class="text-lg font-semibold md:text-xl"
        >
          {{ restaurant.name }}
        </h4>

        <p class="prose max-w-none md:prose-lg">
          {{ restaurant.info }}
        </p>
      </div>
    </template>

    <template v-else>
      <SubHeading>{{ heading }}</SubHeading>

      <p
        class="prose max-w-none md:prose-lg"
        v-html="eatery.info"
      />
    </template>

    <ul
      v-if="eatery.features"
      class="grid grid-cols-1 gap-3 xxs:grid-cols-2 xl:grid-cols-3"
    >
      <li
        v-for="feature in eatery.features"
        :key="feature.slug"
        class="flex items-center gap-2 leading-none"
      >
        <Icon
          :name="feature.slug"
          class="size-8 shrink-0 text-primary lg:size-10"
        />

        <span class="block text-sm leading-none font-semibold md:text-base">
          {{ feature.name }}
        </span>
      </li>
    </ul>

    <p class="text-xs text-grey-dark italic">
      Last updated {{ eatery.last_updated_human }}
    </p>
  </Card>
</template>
