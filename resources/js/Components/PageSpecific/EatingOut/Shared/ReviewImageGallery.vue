<script lang="ts" setup>
import { ReviewImage } from '@/types/EateryTypes';
import { computed, Ref, ref } from 'vue';
import Modal from '@/Components/Overlays/Modal.vue';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid';

const props = withDefaults(
  defineProps<{
    eateryName: string;
    images: ReviewImage[];
    withMargin?: boolean;
    altText?: string;
    limit?: number;
  }>(),
  {
    withMargin: false,
    altText: undefined,
    limit: 0,
  },
);

const viewAll = ref(false);
const displayImage: Ref<false | number> = ref(false);
const touchStart = ref(0);

const goToNextImage = () => {
  if (
    displayImage.value !== false &&
    displayImage.value + 1 >= props.images.length
  ) {
    return;
  }

  displayImage.value = (<number>displayImage.value) += 1;
};

const goToPreviousImage = () => {
  if (displayImage.value === 0 || displayImage.value === false) {
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

const modalKeyEvents = (event: 'addEventListener' | 'removeEventListener') => {
  window[event]('keyup', <EventListener>handleKeyUpEvent);
};

const closeModal = () => {
  modalKeyEvents('removeEventListener');
  displayImage.value = false;
};

const openImage = (index: number) => {
  displayImage.value = index;
  modalKeyEvents('addEventListener');
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

const imageTitle = computed(() => {
  if (props.altText) {
    return props.altText;
  }

  if (displayImage.value && props.images[displayImage.value].location) {
    return `Photo of ${props.images[displayImage.value].location}`;
  }

  if (props.eateryName) {
    return `Photo of ${props.eateryName}`;
  }

  return '';
});
</script>

<template>
  <div>
    <div
      class="flex flex-wrap"
      :class="withMargin ? '' : '-m-2'"
    >
      <div
        v-for="(image, index) in images"
        :key="image.id"
        :class="withMargin ? 'first:-ml-1 lg:first:-ml-2' : ''"
      >
        <img
          v-if="limit === 0 || viewAll === true || index < limit"
          class="max-h-[125px] max-w-[125px] cursor-pointer p-1 lg:p-2 lg:max-2xl:max-h-[200px] lg:max-2xl:max-w-[200px] 2xl:max-h-[250px] 2xl:max-w-[250px]"
          :src="image.thumbnail"
          :alt="altText"
          @click="openImage(index)"
        />
      </div>
    </div>

    <template v-if="limit > 0 && images.length > limit && !viewAll">
      <p
        class="mt-2 cursor-pointer font-semibold text-primary-dark hover:underline"
        @click="viewAll = true"
      >
        Viewing
        <strong
          class="text-black"
          v-text="limit"
        />
        photos out of
        <strong
          class="text-black"
          v-text="images.length"
        />, view all user photos?
      </p>
    </template>

    <Modal
      :open="displayImage !== false"
      no-padding
      size="large"
      closeable
      @close="closeModal()"
    >
      <div
        v-if="imageTitle"
        class="border-grey-mid relative border-b bg-grey-light p-3 pr-[34px] text-center text-sm font-semibold"
        v-html="imageTitle"
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
  </div>
</template>
