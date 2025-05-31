<script lang="ts" setup>
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
    class="my-0! text-left font-coeliac text-2xl font-semibold md:max-lg:text-3xl lg:text-4xl"
    :class="classes"
  >
    <slot />
  </component>
</template>
