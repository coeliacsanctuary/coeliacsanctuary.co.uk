<script setup lang="ts">
defineProps<{ code?: string; country: string }>();
</script>

<template>
  <img
    :src="
      code
        ? `https://flagcdn.com/24x18/${code}.png`
        : '/images/misc/flag-fallback.png'
    "
    :alt="`${country} flag`"
    width="24"
    height="18"
    loading="lazy"
    class="mr-4 h-4.5 w-6 shrink-0 object-cover"
  />
</template>
