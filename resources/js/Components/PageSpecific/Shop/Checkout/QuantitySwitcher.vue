<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    quantity: number;
    size?: 'sm' | 'lg';
  }>(),
  {
    size: 'sm',
  },
);

defineEmits(['alter']);

const classes = computed(() => {
  if (props.size === 'lg') {
    return {
      wrapper: 'h-7',
      button: 'w-8',
      count: 'w-8',
      icon: 'h-4 w-4',
    };
  }

  return {
    wrapper: 'h-5',
    button: 'w-6',
    count: 'w-6',
    icon: 'h-4 w-4',
  };
});
</script>

<template>
  <div
    class="flex space-x-px leading-none font-semibold"
    :class="classes.wrapper"
  >
    <button
      type="button"
      aria-label="Decrease quantity"
      class="flex shrink-0 cursor-pointer items-center justify-center rounded-l-full bg-secondary transition-colors hover:bg-secondary/80"
      :class="classes.button"
      @click="$emit('alter', 'decrease')"
    >
      <MinusIcon :class="classes.icon" />
    </button>

    <div
      class="flex shrink-0 items-center justify-center bg-secondary"
      :class="classes.count"
      v-text="quantity"
    />

    <button
      type="button"
      aria-label="Increase quantity"
      class="flex shrink-0 cursor-pointer items-center justify-center rounded-r-full bg-secondary transition-colors hover:bg-secondary/80"
      :class="classes.button"
      @click="$emit('alter', 'increase')"
    >
      <PlusIcon :class="classes.icon" />
    </button>
  </div>
</template>
