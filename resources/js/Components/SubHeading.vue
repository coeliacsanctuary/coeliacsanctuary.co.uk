<script lang="ts" setup>
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    as?: string;
    classes?: string;
    textSize?: 'xs' | 'small' | 'base' | 'large' | 'xl';
    border?: boolean;
  }>(),
  {
    as: 'h2',
    classes: '',
    textSize: 'base',
    border: false,
  },
);

const headerClasses = computed(() => {
  const baseClasses = ['my-0! text-left font-coeliac font-semibold'];

  if (props.textSize === 'xs') {
    baseClasses.push('text-xl lg:text-[1.7rem]');
  }

  if (props.textSize === 'small') {
    baseClasses.push('text-xl md:max-lg:text-2xl lg:text-3xl');
  }

  if (props.textSize === 'base') {
    baseClasses.push('text-2xl md:max-lg:text-3xl lg:text-4xl');
  }

  if (props.textSize === 'large') {
    baseClasses.push('text-3xl md:max-lg:text-4xl lg:text-5xl');
  }

  if (props.textSize === 'xl') {
    baseClasses.push('text-4xl md:text-5xl');
  }

  if (props.border) {
    baseClasses.push('border-gray-light border-b pb-2');
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
