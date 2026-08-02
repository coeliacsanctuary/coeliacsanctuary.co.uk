<script lang="ts" setup>
import Warning from '@/Components/Warning.vue';
import { formatDate } from '@/helpers';

defineProps<{ lastUpdated: string }>();
</script>

<template>
  <Warning>
    <p class="font-semibold md:text-lg">
      This eatery was unfortunately marked as closed down on
      {{ formatDate(lastUpdated) }}.
    </p>
  </Warning>
</template>
