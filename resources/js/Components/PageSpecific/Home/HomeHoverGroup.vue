<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import HomeHoverItem from '@/Components/PageSpecific/Home/HomeHoverItem.vue';
import { HomeHoverItem as HomeHoverItemType } from '@/types/Types';

withDefaults(
  defineProps<{
    title: string;
    items: HomeHoverItemType[];
    perRow?: number;
  }>(),
  { perRow: 3 },
);
</script>

<template>
  <Card>
    <h2 class="font-coeliac text-3xl font-semibold md:text-5xl">
      {{ title }}
    </h2>

    <div
      class="mx-auto my-2 h-px w-4/5 bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <section
      :class="{
        'sm:grid-cols-3': perRow === 3,
        'sm:grid-cols-4': perRow === 4,
      }"
      class="group grid grid-cols-1 divide-y space-y-5 divide-grey-off sm:divide-y-0 sm:space-y-0"
    >
      <HomeHoverItem
        v-for="item in items"
        :key="item.link"
        :item="item"
      />
    </section>
  </Card>
</template>
