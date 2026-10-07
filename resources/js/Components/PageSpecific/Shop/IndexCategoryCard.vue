<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import { ShopCategoryIndex } from '@/types/Shop';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { useTemplateRef } from 'vue';

const props = defineProps<{
  category: ShopCategoryIndex;
}>();

useJourneyTracking().logWhenVisible(
  useTemplateRef('card'),
  'scrolled_into_view',
  'ShopIndexCategoryCard',
  {
    title: props.category.title,
  },
);
</script>

<template>
  <Card
    ref="card"
    class="group overflow-hidden rounded-lg!"
    no-padding
  >
    <Link
      class="flex h-full flex-col"
      :href="category.link"
      prefetch
    >
      <div class="shrink-0 overflow-hidden">
        <img
          :src="category.image"
          alt=""
          loading="lazy"
          class="w-full transition duration-500 group-hover:scale-105"
        />
      </div>

      <div class="flex flex-1 items-center bg-primary-lightest/60 p-3">
        <h3
          class="line-clamp-2 text-base font-semibold transition group-hover:text-primary-dark"
          v-text="category.title"
        />
      </div>
    </Link>
  </Card>
</template>
