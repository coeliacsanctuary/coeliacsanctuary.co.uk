<script lang="ts" setup>
import { ReviewImage } from '@/types/EateryTypes';
import { computed, onUnmounted, ref, watch } from 'vue';
import Modal from '@/Components/Overlays/Modal.vue';
import ModalHeading from '@/Components/Overlays/ModalHeading.vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

const props = withDefaults(
  defineProps<{
    images: ReviewImage[];
    eateryName?: string;
    altText?: string;
  }>(),
  {
    eateryName: undefined,
    altText: undefined,
  },
);

/** The index of the image being displayed, or false when the lightbox is closed. */
const displayImage = defineModel<number | false>({ required: true });

const touchStart = ref(0);

const goToNextImage = (): void => {
  if (displayImage.value === false) {
    return;
  }

  if (displayImage.value + 1 >= props.images.length) {
    return;
  }

  displayImage.value += 1;
};

const goToPreviousImage = (): void => {
  if (displayImage.value === false || displayImage.value === 0) {
    return;
  }

  displayImage.value -= 1;
};

const closeModal = (): void => {
  displayImage.value = false;
};

const handleKeyUpEvent = (event: KeyboardEvent): void => {
  switch (event.code) {
    case 'ArrowRight':
      goToNextImage();
      break;
    case 'ArrowLeft':
      goToPreviousImage();
      break;
    case 'Escape':
      closeModal();
      break;
    default:
      //
      break;
  }
};

const bindKeyEvents = (
  event: 'addEventListener' | 'removeEventListener',
): void => {
  window[event]('keyup', <EventListener>handleKeyUpEvent);
};

watch(displayImage, (value) => {
  bindKeyEvents(value === false ? 'removeEventListener' : 'addEventListener');
});

onUnmounted(() => bindKeyEvents('removeEventListener'));

const handleTouchStart = (event: TouchEvent): void => {
  touchStart.value = event.changedTouches[0].clientX;
};

const handleTouchEnd = (event: TouchEvent): void => {
  const endPosition = event.changedTouches[0].clientX;

  if (touchStart.value < endPosition) {
    goToPreviousImage();
  }

  if (touchStart.value > endPosition) {
    goToNextImage();
  }
};

const imageTitle = computed(() => {
  if (props.altText) {
    return props.altText;
  }

  if (
    displayImage.value !== false &&
    props.images[displayImage.value].location
  ) {
    return `Photo of ${props.images[displayImage.value].location}`;
  }

  if (props.eateryName) {
    return `Photo of ${props.eateryName}`;
  }

  return '';
});
</script>

<template>
  <Modal
    :open="displayImage !== false"
    no-padding
    size="large"
    closeable
    @close="closeModal()"
  >
    <ModalHeading
      v-if="imageTitle"
      :title="imageTitle"
    />

    <div
      v-if="displayImage !== false"
      class="relative"
    >
      <img
        :src="images[displayImage].path"
        style="max-height: 90vh"
        alt=""
      />

      <div
        class="absolute top-0 left-0 flex h-full w-full justify-between"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
      >
        <div
          class="group w-1/2 cursor-pointer md:max-w-[150px]"
          @click="goToPreviousImage()"
        >
          <div
            v-if="displayImage > 0"
            class="absolute top-0 left-0 flex h-full items-center justify-center bg-black/25 px-4 text-white transition group-hover:bg-black/50"
          >
            <ChevronLeftIcon class="h-6 w-6" />
          </div>
        </div>

        <div
          class="group w-1/2 cursor-pointer md:max-w-[150px]"
          @click="goToNextImage()"
        >
          <div
            v-if="displayImage < images.length - 1"
            class="absolute top-0 right-0 flex h-full items-center justify-center bg-black/25 px-4 text-white transition group-hover:bg-black/50"
          >
            <ChevronRightIcon class="h-6 w-6" />
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>
