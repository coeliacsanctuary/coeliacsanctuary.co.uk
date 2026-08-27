<script setup lang="ts">
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import Icon from '@/Components/Icon.vue';
import { EateryStatistics } from '@/types/EateryTypes';
import { computed } from 'vue';

const props = defineProps<{ statistics: EateryStatistics }>();

const rows = computed(() =>
  [
    {
      icon: 'eatery',
      label: 'Places to eat',
      value: props.statistics.eateries,
    },
    {
      icon: 'dinner',
      label: 'Chain branches',
      value: props.statistics.branches,
    },
    {
      icon: 'attraction',
      label: 'Attractions',
      value: props.statistics.attractions,
    },
    { icon: 'hotel', label: 'Hotels', value: props.statistics.hotels },
    { icon: 'reviews', label: 'Reviews', value: props.statistics.reviews },
  ].filter((row) => row.value > 0),
);
</script>

<template>
  <Card class="flex flex-col space-y-4">
    <SubHeading
      text-size="small"
      class="pb-2"
    >
      Our guide at a glance
    </SubHeading>

    <p class="prose max-w-none">
      Our eating out guide currently lists
      <span class="font-semibold">{{ statistics.total.toLocaleString() }}</span>
      gluten free places across the UK and Ireland.
    </p>

    <ul class="flex flex-col space-y-2">
      <li
        v-for="row in rows"
        :key="row.icon"
        class="flex items-center justify-between rounded bg-primary-lightest/60 px-3 py-2"
      >
        <div class="flex items-center space-x-3">
          <Icon
            :name="row.icon"
            class="size-6 text-primary-dark"
          />

          <span
            class="text-sm font-semibold"
            v-text="row.label"
          />
        </div>

        <span
          class="font-semibold"
          v-text="row.value.toLocaleString()"
        />
      </li>
    </ul>
  </Card>
</template>
