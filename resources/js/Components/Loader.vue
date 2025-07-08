<script lang="ts" setup>
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    absolute?: boolean;
    display: boolean;
    size?: string;
    width?: string;
    color?: 'white' | 'primary' | 'secondary' | 'dark';
    background?: boolean;
    onTop?: boolean;
    blur?: boolean;
    fade?: boolean;
  }>(),
  {
    absolute: true,
    size: 'size-6',
    width: 'border-4',
    color: 'white',
    background: false,
    onTop: false,
    blur: false,
    fade: false,
  },
);

const classes = computed((): string[] => {
  const base = ['animate-spin', 'rounded-full'];

  base.push(props.size, props.width);

  switch (props.color) {
    default:
    case 'white':
      base.push('border-white/20', 'border-t-white/80');
      break;
    case 'secondary':
      base.push('border-secondary/20', 'border-t-secondary/80');
      break;
    case 'primary':
      base.push('border-primary/20', 'border-t-primary/80');
      break;
    case 'dark':
      base.push('border-primary-dark/20', 'border-t-primary-dark/80');
      break;
  }

  return base;
});
</script>

<template>
  <div
    v-if="display"
    class="top-0 left-0 flex h-full w-full items-center justify-center"
    :class="{
      absolute: absolute,
      'bg-black/50': background,
      'bg-white/60': fade,
      'z-999': onTop,
      'backdrop-blur-xs': blur,
    }"
  >
    <div :class="classes" />
  </div>
</template>
