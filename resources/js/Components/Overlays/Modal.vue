<script lang="ts" setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { useSlots } from 'vue';
import OverlayFrame from '@/Components/Overlays/OverlayFrame.vue';

const emit = defineEmits(['close']);

withDefaults(
  defineProps<{
    open: boolean;
    closeable?: boolean;
    noPadding?: boolean;
    size?: 'small' | 'medium' | 'large' | 'xl' | 'full';
    width?: 'w-full' | 'w-auto';
  }>(),
  {
    closeable: true,
    noPadding: false,
    size: 'medium',
    width: 'w-auto',
  },
);

const closeOverlay = () => emit('close');

const slots = useSlots();
</script>

<template>
  <OverlayFrame
    :open="open"
    :width="width"
    :class="{
      'xs:max-w-md': size === 'small',
      'sm:max-w-lg': size === 'medium',
      'sm:max-w-8xl': size === 'large',
      'sm:max-w-[95%]': size === 'full',
    }"
    @close="emit('close')"
  >
    <div
      :class="{ hidden: !closeable }"
      class="absolute right-0 top-0 z-50 pr-2 pt-2"
    >
      <button
        class="rounded-md border border-transparent bg-white/40 text-grey-dark hover:border-grey-dark hover:bg-white/80"
        type="button"
        @click="closeOverlay()"
      >
        <XMarkIcon class="h-6 w-6" />
      </button>
    </div>

    <div :class="{ 'p-2': !noPadding }">
      <slot />
    </div>

    <div
      v-if="slots.footer"
      class="border-t border-grey-off bg-grey-off-light p-2"
    >
      <slot name="footer" />
    </div>
  </OverlayFrame>
</template>
