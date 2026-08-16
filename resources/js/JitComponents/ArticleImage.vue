<script lang="ts" setup>
import { computed, ref, useAttrs, watch } from 'vue';
import { MagnifyingGlassPlusIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Overlays/Modal.vue';
import useGoogleEvents from '@/composables/useGoogleEvents';

const props = withDefaults(
  defineProps<{
    src: string;
    title?: string;
    position?: string;
    width?: string | number;
  }>(),
  { title: undefined, position: 'left', width: undefined },
);

const widthClasses: Record<string, string> = {
  '25': '@2xl:w-1/4',
  '33': '@2xl:w-1/3',
  '50': '@2xl:w-1/2',
  '66': '@2xl:w-2/3',
  '75': '@2xl:w-3/4',
  '100': 'w-full',
};

const zoomed = ref(false);

const isFullWidth = computed((): boolean => props.position === 'fullwidth');

const selectedWidth = computed((): string => {
  const requested = String(props.width ?? '');

  if (requested in widthClasses) {
    return requested;
  }

  return isFullWidth.value ? '100' : '33';
});

const classes = (): string[] => {
  const classList = [
    'my-4',
    'w-full',
    'rounded-sm',
    'bg-primary-lightest/60',
    'p-2',
    widthClasses[selectedWidth.value],
  ];

  if (selectedWidth.value !== '100') {
    if (isFullWidth.value) {
      classList.push('@2xl:mx-auto');
    }

    if (props.position === 'left') {
      classList.push('@2xl:float-left', '@2xl:mr-4');
    }

    if (props.position === 'right') {
      classList.push('@2xl:float-right', '@2xl:ml-4');
    }
  }

  classList.push(<string>useAttrs().class);

  return classList;
};

watch(zoomed, () => {
  if (!zoomed.value) {
    return;
  }

  useGoogleEvents().googleEvent('event', 'article', {
    event_category: 'viewed-image',
    event_label: `viewed-${props.src}`,
  });
});
</script>

<template>
  <figure :class="classes()">
    <div
      class="group relative cursor-zoom-in overflow-hidden rounded-sm"
      @click="zoomed = true"
    >
      <img
        :alt="title"
        :src="src"
        class="m-0! h-auto w-full"
        loading="lazy"
      />

      <div
        class="absolute inset-0 flex items-center justify-center bg-grey-darkest/30 opacity-0 transition group-hover:opacity-100"
      >
        <MagnifyingGlassPlusIcon class="size-10 text-white" />
      </div>
    </div>

    <figcaption
      v-if="title"
      class="mt-2 text-center text-sm text-grey-darker italic"
    >
      {{ title }}
    </figcaption>
  </figure>

  <Modal
    :open="zoomed"
    closeable
    no-padding
    size="full"
    fit-screen
    @close="zoomed = false"
  >
    <img
      :alt="title"
      :src="src"
      class="w-full"
    />

    <template #footer>
      <p
        class="text-sm md:text-base"
        v-html="title"
      />
    </template>
  </Modal>
</template>
