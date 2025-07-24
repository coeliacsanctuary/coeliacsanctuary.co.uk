<script setup lang="ts">
import { onMounted, ref } from 'vue';

withDefaults(defineProps<{ code: string; title?: string }>(), {
  title: 'Sponsored - content continues below',
});

const mounted = ref(false);

onMounted(() => {
  mounted.value = true;

  if (!window.adsbygoogle) {
    return;
  }

  if (!window.__abg_called) {
    try {
      // window.adsbygoogle = window.adsbygoogle || [];
      window.adsbygoogle.push({});
    } catch (e) {
      console.error(e);
    }

    window.__abg_called = true;
  }
});
</script>

<template>
  <div
    v-if="mounted"
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
