<script lang="ts" setup>
import { InformationCircleIcon } from '@heroicons/vue/20/solid';

withDefaults(
  defineProps<{
    noIcon?: boolean;
    theme?: 'primary' | 'light';
    flex?: boolean;
  }>(),
  {
    noIcon: false,
    theme: 'primary',
    flex: false,
  },
);
</script>

<template>
  <div
    class="rounded-sm border-2 p-2 shadow-sm"
    :class="{
      'border-primary bg-primary/20': theme === 'primary',
      'border-primary-light bg-primary-light/20': theme === 'light',
      flex: flex,
    }"
  >
    <div
      v-if="!noIcon"
      class="float-left pr-2"
      :class="{
        'text-primary': theme === 'primary',
        'text-primary-light': theme === 'light',
      }"
    >
      <InformationCircleIcon class="h-10 w-10" />
    </div>

    <slot />
  </div>
</template>
