<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';

defineProps<{
  quantity: number;
}>();

defineEmits(['alter']);
</script>

<template>
  <div class="flex h-5 space-x-px leading-none font-semibold">
    <div
      class="flex w-6 shrink-0 cursor-pointer items-center justify-center rounded-l-full bg-secondary"
      @click="$emit('alter', 'decrease')"
    >
      <MinusIcon class="h-4 w-4" />
    </div>
    <div
      class="flex w-6 shrink-0 items-center justify-center bg-secondary"
      v-text="quantity"
    />
    <div
      class="flex w-6 shrink-0 cursor-pointer items-center justify-center rounded-r-full bg-secondary"
      @click="$emit('alter', 'increase')"
    >
      <PlusIcon class="h-4 w-4" />
    </div>
  </div>
</template>
