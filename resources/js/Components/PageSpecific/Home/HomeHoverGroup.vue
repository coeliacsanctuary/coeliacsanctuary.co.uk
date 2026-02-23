<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import HomeHoverItem from '@/Components/PageSpecific/Home/HomeHoverItem.vue';
import { HomeHoverItem as HomeHoverItemType } from '@/types/Types';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = withDefaults(
  defineProps<{
    title: string;
    items: HomeHoverItemType[];
    perRow?: number;
  }>(),
  { perRow: 3 },
);

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'Home/HoverGroup',
  {
    group: props.title,
  },
);
</script>

<template>
  <Card ref="card">
    <h2 class="font-coeliac text-3xl font-semibold md:text-5xl">
      {{ title }}
    </h2>

    <div
      class="mx-auto my-2 h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <section
      :class="{
        'sm:grid-cols-3': perRow === 3,
        'sm:grid-cols-4': perRow === 4,
      }"
      class="group grid grid-cols-1 space-y-5 divide-y divide-grey-off sm:space-y-0 sm:divide-y-0"
    >
      <HomeHoverItem
        v-for="item in items"
        :key="item.link"
        :item="item"
      />
    </section>
  </Card>
</template>
