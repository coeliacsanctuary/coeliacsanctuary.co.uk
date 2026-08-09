<script lang="ts" setup>
import { computed, Ref, ref } from 'vue';
import { XMarkIcon, PlusIcon } from '@heroicons/vue/24/outline';
import axios, { AxiosResponse } from 'axios';
import Loader from '@/Components/Loader.vue';

type ProcessingImage = {
  processing: true;
};

type UploadableImage = {
  processing: false;
  id: string;
  path: string;
};

const images: Ref<Array<ProcessingImage | UploadableImage>> = ref([]);
const fileTrigger: Ref<HTMLInputElement | null> = ref(null);

const imageClasses = computed(() => [
  'border',
  'border-primary-dark',
  'flex',
  'm-2',
  'h-[97.75px]',
  'items-center',
  'justify-center',
  'rounded-sm',
  'w-[130px]',
  'xxs:h-[112.5px]',
  'xxs:w-[150px]',
]);

const imageOverlayClasses = computed(() => [
  'w-full',
  'h-full',
  'opacity-0',
  'transition',
  'transition-opacity',
  'bg-black/50',
  'group-hover:opacity-100',
  'absolute',
  'top-1/2',
  'left-1/2',
  'transform',
  '-translate-x-1/2',
  '-translate-y-1/2',
  'text-red',
  'flex',
  'items-center',
  'justify-center',
]);

const emits = defineEmits(['images-change', 'error']);

const validateImage = (image: File): boolean => {
  const validMimes = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];

  if (!validMimes.includes(image.type.toLowerCase())) {
    return false;
  }

  if (image.size >= 5120000) {
    return false;
  }

  return true;
};

const pushImage = (): number => {
  const location = images.value.length;
  images.value = [...images.value, { processing: true }];

  return location;
};

const displayImage = (image: UploadableImage, index: number): void => {
  const currentImages: Array<UploadableImage | ProcessingImage> = images.value;
  currentImages[index] = <UploadableImage>{
    ...image,
    processing: false,
  };

  images.value = currentImages;
};

const processedImages = (): UploadableImage[] =>
  images.value.filter(
    (image: ProcessingImage | UploadableImage) => !image.processing,
  );

const emitChange = (): void => {
  emits(
    'images-change',
    processedImages().map((image: UploadableImage) => image.id),
  );
};

const emitError = (error: string): void => {
  emits('error', error);

  images.value = images.value.filter((image) => !image.processing);
};

const processImages = (): void => {
  const files = fileTrigger.value?.files;

  if (!files) {
    return;
  }

  const data: FormData = new FormData();
  const mapping: number[] = [];

  for (let x = 0; x < files.length; x += 1) {
    if (!validateImage(files[x])) {
      emitError('There was an error uploading one of your images...');

      break;
    }

    if (x >= 6) {
      emitError('You can only upload 6 images');

      break;
    }

    data.append(`images[${x}]`, files[x]);
    mapping.push(pushImage());
  }

  axios
    .post('/api/wheretoeat/review/image-upload', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    .then((response: AxiosResponse<{ images: UploadableImage[] }>) => {
      response.data.images.forEach((image: UploadableImage, index: number) => {
        displayImage(image, mapping[index]);
      });

      emitChange();
    })
    .catch(() => {
      emitError('There was an error uploading your image(s)');
    });
};

const uploadImage = (): void => {
  fileTrigger.value?.click();
};

const deleteImage = (id: string) => {
  images.value = images.value.filter(
    (image: ProcessingImage | UploadableImage) =>
      !image.processing && image.id !== id,
  );

  emitChange();
};
</script>

<template>
  <div class="flex-1">
    <span class="block text-lg font-semibold text-primary-dark">
      Do you want to attach up to six (6) images with your review?
    </span>

    <div class="mt-2 rounded-lg border border-grey-off p-2">
      <input
        ref="fileTrigger"
        type="file"
        class="hidden"
        accept="image/*"
        multiple
        @change="processImages()"
      />

      <ul class="-m-2 flex flex-wrap">
        <li
          v-for="(image, index) in images"
          :key="index"
          :class="[...imageClasses, images.length > 0 ? 'initial' : 'hidden']"
        >
          <div
            v-if="image.processing"
            class="h-[100px] w-[100px]"
          >
            <Loader
              :display="true"
              size="size-16"
              color="primary"
              :absolute="false"
              width="border-[6px]"
            />
          </div>

          <div
            v-else
            class="group relative h-full w-full cursor-pointer text-8xl text-primary-dark"
          >
            <img
              class="h-full w-full object-cover"
              :src="image.path"
              alt=""
            />

            <div
              :class="imageOverlayClasses"
              @click="deleteImage(image.id)"
            >
              <XMarkIcon class="h-6 w-6" />
            </div>
          </div>
        </li>

        <li
          v-if="images.length < 6"
          :class="imageClasses"
          class="cursor-pointer text-6xl text-primary-dark"
          @click="uploadImage()"
        >
          <PlusIcon class="h-16 w-16" />
        </li>
      </ul>

      <small class="text-xs leading-none text-grey-off-dark">
        Maximum file size: 5mb
      </small>
    </div>
  </div>
</template>
