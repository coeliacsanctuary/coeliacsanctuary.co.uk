<script setup lang="ts">
withDefaults(
  defineProps<{
    code?: string;
    country: string;
    width?: string;
    height?: string;
  }>(),
  {
    code: undefined,
    width: '24',
    height: '18',
  },
);
</script>

<template>
  <img
    :src="
      code
        ? `https://flagcdn.com/${width}x${height}/${code}.png`
        : '/images/misc/flag-fallback.png'
    "
    :alt="`${country} flag`"
    :width="width"
    :height="height"
    loading="lazy"
    class="mr-4 shrink-0 object-cover"
  />
</template>
