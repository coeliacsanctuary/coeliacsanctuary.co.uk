<script lang="ts" setup>
import { HeadingBackLink } from '@/types/Types';
import { Link } from '@inertiajs/vue3';

withDefaults(
  defineProps<{
    as?: string;
    classes?: string;
  }>(),
  {
    as: 'h2',
    classes: '',
  },
);
</script>

<template>
  <component
    :is="as"
    class="text-left font-coeliac text-2xl font-semibold md:max-lg:text-3xl lg:text-4xl my-0!"
    :class="classes"
  >
    <slot />
  </component>
</template>
