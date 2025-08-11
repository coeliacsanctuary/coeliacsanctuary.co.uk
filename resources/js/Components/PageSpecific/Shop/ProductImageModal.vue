<script setup lang="ts">
import { ShopProductDetail } from '@/types/Shop';
import Modal from '@/Components/Overlays/Modal.vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';
import { computed, onMounted, ref, watch } from 'vue';

const props = withDefaults(
  defineProps<{
    title: ShopProductDetail['title'];
    primaryImage: ShopProductDetail['image'];
    additionalImages: ShopProductDetail['additional_images'];
    open?: boolean;
    currentImage?: number;
  }>(),
  { open: false, currentImage: undefined },
);

const emit = defineEmits(['close']);

const displayImage = ref(props.currentImage);

const imagePath = computed(() => {
  if (displayImage.value === undefined) {
    return undefined;
  }

  if (displayImage.value === -1 || !props.additionalImages) {
    return props.primaryImage;
  }

  return props.additionalImages[displayImage.value];
});

const touchStart = ref(0);

const goToNextImage = () => {
  if (!props.additionalImages) {
    return;
  }

  if (
    displayImage.value &&
    displayImage.value + 1 >= props.additionalImages.length
  ) {
    return;
  }

  displayImage.value = (<number>displayImage.value) += 1;
};

const goToPreviousImage = () => {
  if (!props.additionalImages) {
    return;
  }

  if (displayImage.value === -1 || displayImage.value === undefined) {
    return;
  }

  displayImage.value -= 1;
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
      // eslint-disable-next-line @typescript-eslint/no-use-before-define
      closeModal();
      break;
    default:
      //
      break;
  }
};

onMounted(() => {
  modalKeyEvents('addEventListener');
});

const modalKeyEvents = (event: 'addEventListener' | 'removeEventListener') => {
  window[event]('keyup', <EventListener>handleKeyUpEvent);
};

const closeModal = () => {
  modalKeyEvents('removeEventListener');
  emit('close');
};

const handleTouchStart = (event: TouchEvent) => {
  touchStart.value = event.changedTouches[0].clientX;
};

const handleTouchEnd = (event: TouchEvent) => {
  const endPosition = event.changedTouches[0].clientX;

  if (touchStart.value < endPosition) {
    goToPreviousImage();
  }

  if (touchStart.value > endPosition) {
    goToNextImage();
  }
};

watch(
  () => props.currentImage,
  () => {
    displayImage.value = props.currentImage;
  },
);
</script>

<template>
  <Modal
    :open="open"
    size="full"
    no-padding
    @close="$emit('close')"
  >
    <div class="relative">
      <img
        :src="imagePath"
        :alt="title"
      />

      <div
        v-if="additionalImages?.length"
        class="absolute top-0 left-0 flex h-full w-full justify-between"
        @touchstart="handleTouchStart($event)"
        @touchend="handleTouchEnd($event)"
      >
        <div
          class="group w-1/2 cursor-pointer md:max-w-[150px]"
          @click="goToPreviousImage()"
        >
          <div
            v-if="displayImage !== undefined && displayImage > -1"
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
            v-if="
              displayImage !== undefined &&
              displayImage < additionalImages.length - 1
            "
            class="absolute top-0 right-0 flex h-full items-center justify-center bg-black/25 px-4 text-white transition group-hover:bg-black/50"
          >
            <ChevronRightIcon class="h-6 w-6" />
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <span
        class="text-xs"
        v-text="title"
      />
    </template>
  </Modal>
</template>
