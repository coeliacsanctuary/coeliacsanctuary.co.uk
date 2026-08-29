<script lang="ts" setup>
import { ReviewImage } from '@/types/EateryTypes';
import { Ref, ref } from 'vue';
import ReviewImageModal from '@/Components/PageSpecific/EatingOut/Shared/ReviewImageModal.vue';

withDefaults(
  defineProps<{
    eateryName: string;
    images: ReviewImage[];
    altText?: string;
    limit?: number;
  }>(),
  {
    altText: undefined,
    limit: 0,
  },
);

const viewAll = ref(false);
const displayImage: Ref<false | number> = ref(false);
</script>

<template>
  <div>
    <div class="flex flex-wrap gap-2">
      <div
        v-for="(image, index) in images"
        v-show="limit === 0 || viewAll || index < limit"
        :key="image.id"
        class="group relative size-20 cursor-pointer overflow-hidden rounded-sm sm:size-24 lg:size-28"
        @click="displayImage = index"
      >
        <img
          class="h-full w-full object-cover transition group-hover:scale-105"
          :src="image.thumbnail"
          :alt="altText"
          loading="lazy"
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

    <ReviewImageModal
      v-model="displayImage"
      :images="images"
      :eatery-name="eateryName"
      :alt-text="altText"
    />
  </div>
</template>
