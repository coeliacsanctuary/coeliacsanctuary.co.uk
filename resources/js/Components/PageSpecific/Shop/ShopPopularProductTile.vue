<script setup lang="ts">
import Card from '@/Components/Card.vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';
import { ShopPopularProduct } from '@/types/Shop';
import { pluralise } from '@/helpers';
import { computed } from 'vue';

const props = defineProps<{ product: ShopPopularProduct }>();

const ratingText = computed((): string | null => {
  if (!props.product.rating) {
    return null;
  }

  const label = pluralise('rating', props.product.rating.count);

  return `${props.product.rating.count} ${label}`;
});
</script>

<template>
  <Card
    class="group overflow-hidden rounded-lg!"
    no-padding
  >
    <Link
      :href="product.link"
      class="flex h-full flex-col"
      prefetch
    >
      <div class="shrink-0 overflow-hidden">
        <img
          :alt="product.title"
          :src="product.image"
          loading="lazy"
          width="1200"
          height="630"
          class="aspect-[1200/630] w-full object-cover transition duration-500 group-hover:scale-105"
        />
      </div>

      <div class="flex flex-1 flex-col space-y-2 bg-primary-lightest/60 p-3">
        <h3
          class="line-clamp-2 text-base font-semibold transition group-hover:text-primary-dark"
          v-text="product.title"
        />

        <div
          v-if="product.rating && product.rating.count > 0"
          class="flex flex-wrap items-center gap-2"
        >
          <StarRating
            :rating="product.rating.average"
            size="w-4 h-4"
            show-all
          />

          <span
            class="text-sm font-semibold text-grey-dark"
            v-text="ratingText"
          />
        </div>

        <span
          class="mt-auto pt-1 text-lg font-semibold text-primary-dark"
          v-text="product.price"
        />
      </div>
    </Link>
  </Card>
</template>
