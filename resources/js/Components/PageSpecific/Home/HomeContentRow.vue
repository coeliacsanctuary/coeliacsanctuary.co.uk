<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import SubHeading from '@/Components/SubHeading.vue';
import SnapScrollRow from '@/Components/SnapScrollRow.vue';
import HomeContentTile from '@/Components/PageSpecific/Home/HomeContentTile.vue';
import { HomeHoverItem as HomeHoverItemType } from '@/types/Types';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { computed, useTemplateRef } from 'vue';

const props = withDefaults(
  defineProps<{
    title: string;
    items: HomeHoverItemType[];
    viewAllHref: string;
    viewAllLabel: string;
    perRow?: number;
  }>(),
  { perRow: 3 },
);

const gridClasses = computed(() =>
  props.perRow === 4 ? 'sm:grid-cols-4' : 'sm:grid-cols-3',
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
  <Card
    ref="card"
    class="space-y-4"
  >
    <div class="flex flex-wrap items-center justify-between gap-2">
      <SubHeading
        as="h2"
        text-size="small"
      >
        {{ title }}
      </SubHeading>

      <Link
        :href="viewAllHref"
        class="inline-flex items-center space-x-2 font-semibold text-primary-dark transition hover:text-black"
        prefetch
      >
        <span v-text="viewAllLabel" />

        <ArrowRightIcon class="size-4" />
      </Link>
    </div>

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <div v-if="$slots.default">
      <slot />
    </div>

    <SnapScrollRow
      :items="items"
      :grid-classes="gridClasses"
      :label="(item) => `Show ${item.title}`"
    >
      <template #default="{ item, itemClasses }">
        <HomeContentTile
          :item="item"
          :class="itemClasses"
        />
      </template>
    </SnapScrollRow>
  </Card>
</template>
