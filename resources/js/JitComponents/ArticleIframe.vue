<script setup lang="ts">
defineOptions({
  inheritAttrs: false,
});
</script>

<template>
  <div>
    <iframe v-bind="$attrs" />
  </div>
</template>
