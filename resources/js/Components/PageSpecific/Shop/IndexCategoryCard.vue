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
    theme="transparent"
    no-flex
    no-padding
    :shadow="false"
  >
    <Link
      class="group block h-full"
      :href="category.link"
      prefetch
    >
      <div class="relative flex h-full flex-col overflow-hidden rounded-sm">
        <div>
          <img
            :src="category.image"
            alt=""
            class="h-full w-full object-cover object-center"
          />
        </div>

        <div
          class="flex flex-1 flex-col space-y-2 rounded-br-lg rounded-bl-lg bg-primary-alt/75 p-4 backdrop-blur-sm backdrop-filter"
        >
          <div class="flex-1">
            <h2
              class="text-xl font-semibold text-white xmd:max-xl:text-2xl xl:text-3xl"
              v-text="category.title"
            />
          </div>

          <div
            class="flex shrink-0 items-center justify-center rounded-md border border-white/25 bg-primary-alt/0 px-4 py-3 text-base font-medium text-white transition group-hover:bg-primary-dark/50 xl:text-lg"
          >
            Shop Now
          </div>
        </div>
      </div>
    </Link>
  </Card>
</template>
