<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import { ShopCategoryIndex } from '@/types/Shop';
import useJourneyTracking from '@/composables/useJourneyTracking';
import { computed, useTemplateRef } from 'vue';
import { pluralise } from '@/helpers';

const props = defineProps<{
  category: ShopCategoryIndex;
}>();

const stats = computed((): string[] => {
  const { products_count: products, price } = props.category;

  return [
    products ? `${products} ${pluralise('product', products)}` : null,
    price,
  ].filter((stat) => !!stat);
});

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
      class="group block px-4 sm:max-2xl:px-6 2xl:px-0"
      :href="category.link"
      prefetch
    >
      <div class="relative overflow-hidden rounded-sm">
        <img
          :src="category.image"
          alt=""
          class="h-full w-full object-cover object-center"
        />

        <div
          v-if="stats.length"
          class="absolute top-0 right-0 hidden p-3 sm:block"
        >
          <p
            class="rounded-sm bg-primary-light/90 px-3 py-2 font-semibold shadow-lg lg:text-lg"
            v-text="stats.join(', ')"
          />
        </div>
        <div
          class="rounded-br-lg rounded-bl-lg bg-primary-alt/75 p-4 backdrop-blur-sm backdrop-filter xs:flex xs:items-center xs:justify-between xmd:absolute xmd:inset-x-auto xmd:inset-y-0 xmd:w-84 xmd:flex-col xmd:items-start xmd:rounded-tl-lg xmd:rounded-br-none xmd:p-6"
        >
          <div>
            <h2
              class="text-xl font-semibold text-white xmd:max-xl:text-2xl xl:text-3xl"
              v-text="category.title"
            />
            <p
              class="mt-1 text-sm text-gray-300 xmd:max-xl:mt-2 xmd:max-xl:text-base xl:mt-3 xl:text-lg"
              v-text="category.description"
            />
          </div>

          <div
            class="mt-6 flex shrink-0 items-center justify-center rounded-md border border-white/25 bg-primary-alt/0 px-4 py-3 text-base font-medium text-white transition group-hover:bg-primary-dark/50 xs:mt-0 xs:max-xmd:ml-8 xmd:ml-0 xmd:w-full xl:text-lg"
          >
            Shop Now
          </div>
        </div>
      </div>
    </Link>
  </Card>
</template>
