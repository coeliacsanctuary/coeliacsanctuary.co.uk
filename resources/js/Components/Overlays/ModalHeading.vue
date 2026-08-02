<script lang="ts" setup>
defineProps<{ title: string }>();
</script>

<template>
  <div
    class="relative border-b border-grey-off bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
    v-html="title"
  />
</template>
