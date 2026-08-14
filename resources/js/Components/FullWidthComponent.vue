<script setup lang="ts">
withDefaults(
  defineProps<{ wrapperClasses?: string; innerClasses?: string }>(),
  { wrapperClasses: '', innerClasses: '' },
);
</script>

<template>
  <div
    class="mx-[calc(50%-50vw)]"
    :class="wrapperClasses"
  >
    <div
      class="mx-auto max-w-8xl"
      :class="innerClasses"
    >
      <slot />
    </div>
  </div>
</template>
