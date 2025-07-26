<script lang="ts" setup>
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    as?: string;
    classes?: string;
    textSize?: 'base' | 'large' | 'xl';
  }>(),
  {
    as: 'h2',
    classes: '',
    textSize: 'base',
  },
);

const headerClasses = computed(() => {
  const baseClasses = ['my-0! text-left font-coeliac font-semibold'];

  if (props.textSize === 'base') {
    baseClasses.push('text-2xl md:max-lg:text-3xl lg:text-4xl');
  }

  if (props.textSize === 'large') {
    baseClasses.push('text-3xl md:max-lg:text-4xl lg:text-5xl');
  }

  if (props.textSize === 'xl') {
    baseClasses.push('text-4xl md:text-5xl');
  }

  baseClasses.push(props.classes);

  return baseClasses;
});
</script>

<template>
  <component
    :is="as"
    :class="headerClasses"
  >
    <slot />
  </component>
</template>
