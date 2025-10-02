<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

withDefaults(defineProps<{ code: string; title?: string }>(), {
  title: 'Sponsored - content continues below',
});

const mounted = ref(false);

const url = computed(() => usePage().url);

watch(url, async () => {
  await renderAd();
});

const renderAd = async () => {
  await nextTick();

  const tryInjectAd = () => {
    try {
      (window.adsbygoogle = window.adsbygoogle || []).push({});
    } catch (e) {
      console.error('Adsbygoogle push failed', e);
    }
  };

  if (window.adsbygoogle) {
    tryInjectAd();

    return;
  }

  // Poll every 100ms for up to 3 seconds
  const maxRetries = 30;
  let retries = 0;

  const interval = setInterval(() => {
    if (window.adsbygoogle) {
      clearInterval(interval);
      tryInjectAd();
    }

    if (++retries >= maxRetries) {
      clearInterval(interval);
      console.warn('adsbygoogle not available after timeout');
    }
  }, 100);
};

onMounted(async () => {
  mounted.value = true;

  await renderAd();
});
</script>

<template>
  <div
    v-if="mounted"
    :key="`${url}-${code}`"
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
