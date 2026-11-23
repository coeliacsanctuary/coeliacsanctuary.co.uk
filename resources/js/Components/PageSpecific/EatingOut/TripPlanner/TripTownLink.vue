<script setup lang="ts">
import { TripTown } from '@/types/TripPlannerTypes';
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline';

defineProps<{
  town: TripTown;
}>();
</script>

<template>
  <a
    :href="town.link"
    target="_blank"
    class="flex items-center gap-1 p-2 text-sm font-semibold text-primary-dark hover:underline"
  >
    View more places in {{ town.name }}

    <ArrowTopRightOnSquareIcon class="size-4" />
  </a>
</template>
