<script lang="ts" setup>
import CoeliacCompactHeader from '@/Layouts/Components/CoeliacCompactHeader.vue';
import CoeliacCompactFooter from '@/Layouts/Components/CoeliacCompactFooter.vue';
import { MetaProps } from '@/types/DefaultProps';
import useStickyAdOffset from '@/composables/useStickyAdOffset';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const { adhesionHeight } = useStickyAdOffset();

defineProps<{ meta: MetaProps }>();

const footerEl = ref<HTMLElement | null>(null);
let footerObserver: ResizeObserver | null = null;

const repositionVideo = (): void => {
  const videoEl = document.getElementById('universalPlayer_wrapper');

  if (!videoEl || !footerEl.value) {
    return;
  }

  const offset = adhesionHeight.value + footerEl.value.offsetHeight;
  videoEl.style.setProperty('bottom', `${offset}px`, 'important');
};

watch(adhesionHeight, repositionVideo);

onMounted(() => {
  footerObserver = new ResizeObserver(repositionVideo);

  if (footerEl.value) {
    footerObserver.observe(footerEl.value);
  }

  repositionVideo();
});

onUnmounted(() => {
  footerObserver?.disconnect();
});
</script>

<template>
  <div
    class="flex h-screen flex-col bg-gray-100"
    style="padding-bottom: var(--sticky-bottom, 0px)"
  >
    <CoeliacCompactHeader :metas="meta" />

    <section class="mx-auto flex w-full flex-1 flex-col space-y-3">
      <slot />
    </section>

    <CoeliacCompactFooter ref="footerEl" />
  </div>
</template>
