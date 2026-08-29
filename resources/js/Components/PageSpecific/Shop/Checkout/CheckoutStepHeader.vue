<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/24/outline';
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

const props = defineProps<{
  step: number;
  title: string;
  show: boolean;
  completed: boolean;
  error?: boolean;
  summary?: string[];
}>();

defineEmits(['toggle']);

const canToggle = computed(() => props.completed && !props.show);

const markerClasses = computed(() => {
  if (props.error) {
    return 'border-red bg-red text-white';
  }

  if (props.completed) {
    return 'border-primary-dark bg-primary-dark text-white';
  }

  if (props.show) {
    return 'border-primary-dark text-primary-dark';
  }

  return 'border-grey-off text-grey-off';
});
</script>

<template>
  <div class="flex flex-col gap-2">
    <div
      class="flex items-center gap-3"
      :class="{ 'cursor-pointer': canToggle }"
      @click="canToggle ? $emit('toggle') : undefined"
    >
      <span
        class="flex size-8 shrink-0 items-center justify-center rounded-full border-2 font-semibold transition-colors"
        :class="markerClasses"
      >
        <CheckIcon
          v-if="completed && !error"
          class="size-5"
        />
        <ExclamationCircleIcon
          v-else-if="error"
          class="size-5"
        />
        <template v-else>{{ step }}</template>
      </span>

      <h2
        class="flex-1 text-2xl font-semibold sm:text-3xl"
        :class="{
          'text-primary-dark': !error && (show || completed),
          'text-grey-off': !error && !show && !completed,
          'text-red': error,
        }"
        v-text="title"
      />

      <button
        v-if="canToggle"
        type="button"
        class="shrink-0 text-sm font-semibold text-grey-dark underline underline-offset-2 hover:text-primary-dark"
        @click.stop="$emit('toggle')"
      >
        Edit
      </button>
    </div>

    <div
      v-if="summary?.length && !show"
      class="flex flex-col pl-11 text-sm text-grey-dark"
    >
      <span
        v-for="(line, index) in summary"
        :key="index"
        v-text="line"
      />
    </div>
  </div>
</template>
