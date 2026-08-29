<script lang="ts" setup>
import eventBus from '@/eventBus';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{ article?: HTMLElement }>();

const progress = ref(0);

const stickyNav = ref(false);

/**
 * How far the reader has scrolled through the article itself, ignoring
 * everything above and below it so the comments don't read as unread article.
 */
const updateProgress = (): void => {
  if (!props.article) {
    return;
  }

  const { top, height } = props.article.getBoundingClientRect();

  const scrolled = -top;
  const scrollable = height - window.innerHeight;

  if (scrollable <= 0) {
    progress.value = scrolled > 0 ? 100 : 0;

    return;
  }

  progress.value = Math.min(100, Math.max(0, (scrolled / scrollable) * 100));
};

onMounted(() => {
  updateProgress();

  window.addEventListener('scroll', updateProgress, { passive: true });
  window.addEventListener('resize', updateProgress, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', updateProgress);
  window.removeEventListener('resize', updateProgress);
});

eventBus.$on('sticky-nav-on', () => (stickyNav.value = true));
eventBus.$on('sticky-nav-off', () => (stickyNav.value = false));
</script>

<template>
  <div
    class="fixed top-0 left-0 z-[99999] h-1 w-full bg-transparent"
    :class="{ 'md:top-12': stickyNav }"
  >
    <div
      class="h-full bg-secondary transition-[width] duration-150 ease-out"
      :style="{ width: `${progress}%` }"
      role="progressbar"
      aria-label="Reading progress"
      :aria-valuenow="Math.round(progress)"
      aria-valuemin="0"
      aria-valuemax="100"
    />
  </div>
</template>
