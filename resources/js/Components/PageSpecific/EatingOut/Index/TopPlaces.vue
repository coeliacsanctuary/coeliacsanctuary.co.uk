<script setup lang="ts">
import CollapsibleCard from '@/Components/CollapsibleCard.vue';
import { BreakPoint } from '@/composables/useScreensize';

withDefaults(
  defineProps<{
    showBreakpoint?: BreakPoint;
    collapsible?: boolean;
  }>(),
  {
    showBreakpoint: 'md',
    collapsible: true,
  },
);
</script>

<template>
  <CollapsibleCard
    :show-breakpoint="showBreakpoint"
    :collapsible="collapsible"
  >
    <template #title>
      <slot name="title" />
    </template>

    <slot />
  </CollapsibleCard>
</template>
