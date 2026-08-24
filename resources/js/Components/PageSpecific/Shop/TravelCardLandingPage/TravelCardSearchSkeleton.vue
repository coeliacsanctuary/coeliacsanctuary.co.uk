<script setup lang="ts">
import Card from '@/Components/Card.vue';
</script>

<template>
  <Card class="space-y-4">
    <div class="h-8 w-2/3 animate-pulse rounded-sm bg-primary-light/50 sm:w-1/3" />

    <div
      class="mx-auto h-px w-full bg-linear-to-r from-secondary/40 via-secondary/60 to-secondary/40"
    />

    <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
      <div
        v-for="tile in 3"
        :key="tile"
        class="flex animate-pulse flex-col space-y-3"
      >
        <div class="aspect-[1200/630] w-full rounded-sm bg-primary-light/40" />

        <div class="h-6 w-3/4 rounded-sm bg-primary-light/40" />

        <div class="h-4 w-full rounded-sm bg-primary-light/30" />

        <div class="h-4 w-5/6 rounded-sm bg-primary-light/30" />

        <div class="h-10 w-full rounded-sm bg-primary-light/40" />
      </div>
    </div>
  </Card>
</template>
