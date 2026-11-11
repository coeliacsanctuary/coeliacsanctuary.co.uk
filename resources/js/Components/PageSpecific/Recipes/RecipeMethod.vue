<script lang="ts" setup>
defineProps<{ method: string }>();

const classes = [
  'prose prose-lg max-w-none md:prose-xl',
  '[&_ol]:list-decimal [&_ol]:pl-9',
  '[&_ol>li]:pl-1',
  '[&_ol>li+li]:mt-3',
  '[&_ol>li::marker]:font-bold [&_ol>li::marker]:text-[1.15em] [&_ol>li::marker]:text-primary-dark',
];
</script>

<template>
  <article
    :class="classes"
    v-html="method"
  />
</template>
