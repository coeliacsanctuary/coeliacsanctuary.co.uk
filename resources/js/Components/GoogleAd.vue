<script setup lang="ts">
import { computed, onMounted } from 'vue';

withDefaults(defineProps<{ code: string; title?: string }>(), {
  title: 'Sponsored - content continues below',
});

const hasGoogle = computed(() => {
  if (typeof window === 'undefined') {
    return false;
  }

  if (!window.adsbygoogle) {
    return false;
  }

  return true;
});

onMounted(() => {
  if (!hasGoogle.value) {
    return;
  }

  (adsbygoogle = window.adsbygoogle || []).push({});
});
</script>

<template>
  <div
    v-if="hasGoogle"
    class="m-2 flex flex-col border-y border-primary-light py-2 text-center"
  >
    <p
      class="mb-2 text-left text-xs font-semibold"
      v-text="title"
    />
    <ins
      class="adsbygoogle"
      style="display: block; width: 100%"
      data-ad-client="ca-pub-1063051842575021"
      :data-ad-slot="code"
      data-ad-format="auto"
      data-full-width-responsive="true"
    />
  </div>
</template>
