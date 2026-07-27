<script lang="ts" setup>
import { AdjustmentsHorizontalIcon } from '@heroicons/vue/24/solid';
import Sidebar from '@/Components/Overlays/Sidebar.vue';
import { onMounted, onUnmounted, ref } from 'vue';
import TownFilterSidebarContent from '@/Components/PageSpecific/EatingOut/Town/TownFilterSidebarContent.vue';
import useScreensize from '@/composables/useScreensize';
import { EateryFilters } from '@/types/EateryTypes';

const props = withDefaults(
  defineProps<{
    filters: EateryFilters;
    fixed?: boolean;
  }>(),
  {
    fixed: false,
  },
);

const viewSidebar = ref(false);

const { screenIsGreaterThanOrEqualTo } = useScreensize();

defineEmits(['filtersUpdated', 'sidebarClosed']);

/** The height of the sticky nav, plus a small gap. */
const stickyOffset = 56;

const stickyColumn = ref<HTMLElement | null>(null);
const top = ref(stickyOffset);

let lastScrollY = 0;
let ticking = false;

/**
 * Move the sticky offset with the scroll, clamped between the top of the
 * viewport (under the nav) and the offset that bottom aligns the column, so a
 * column taller than the viewport scrolls with the content rather than having
 * its bottom clipped off screen.
 */
const updateOffset = (): void => {
  ticking = false;

  if (!stickyColumn.value) {
    return;
  }

  const delta = window.scrollY - lastScrollY;

  lastScrollY = window.scrollY;

  const bottomOffset = window.innerHeight - stickyColumn.value.offsetHeight - 8;

  top.value = Math.min(
    stickyOffset,
    Math.max(Math.min(stickyOffset, bottomOffset), top.value - delta),
  );
};

const handleScroll = (): void => {
  if (ticking) {
    return;
  }

  ticking = true;

  window.requestAnimationFrame(updateOffset);
};

onMounted(() => {
  if (!props.fixed) {
    return;
  }

  lastScrollY = window.scrollY;

  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('resize', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('resize', handleScroll);
});
</script>

<template>
  <div
    class="safe-bottom fixed right-0 z-10 p-4 xmd:hidden"
    style="bottom: var(--sticky-bottom-right, 0px)"
  >
    <div
      class="-ml-3 rounded-full border-2 border-white bg-primary p-3 text-white shadow-sm transition"
    >
      <AdjustmentsHorizontalIcon
        class="h-8 w-8"
        @click="viewSidebar = true"
      />
    </div>
  </div>

  <template v-if="screenIsGreaterThanOrEqualTo('xmd')">
    <div
      ref="stickyColumn"
      :class="{ 'xmd:sticky xmd:h-fit': fixed }"
      :style="fixed ? { top: `${top}px` } : undefined"
    >
      <TownFilterSidebarContent
        :filters="filters"
        @updated="$emit('filtersUpdated', $event)"
      />
    </div>

    <div class="content_desktop_hint"></div>
  </template>

  <Sidebar
    v-else
    :open="viewSidebar"
    side="right"
    @close="
      viewSidebar = false;
      $emit('sidebarClosed');
    "
  >
    <TownFilterSidebarContent
      class="!h-full"
      :filters="filters"
      @updated="$emit('filtersUpdated', $event)"
    />
  </Sidebar>
</template>
