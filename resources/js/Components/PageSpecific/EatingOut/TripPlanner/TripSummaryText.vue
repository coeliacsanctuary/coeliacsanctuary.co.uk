<script setup lang="ts">
defineProps<{
  pending: boolean;
  html?: string | null;
  label?: string;
}>();
</script>

<template>
  <div
    v-if="pending || html"
    class="m-2 flex flex-col space-y-1 rounded-md bg-primary-lightest p-3"
  >
    <p
      v-if="label"
      class="text-sm font-semibold text-grey-darker"
      v-text="label"
    />

    <div
      v-if="pending"
      class="flex animate-pulse flex-col space-y-2 py-1"
    >
      <div class="h-3 w-full rounded-sm bg-primary-light/60" />
      <div class="h-3 w-full rounded-sm bg-primary-light/60" />
      <div class="h-3 w-2/3 rounded-sm bg-primary-light/60" />
    </div>

    <div
      v-else
      class="prose prose-sm max-w-none"
      v-html="html"
    />
  </div>
</template>
