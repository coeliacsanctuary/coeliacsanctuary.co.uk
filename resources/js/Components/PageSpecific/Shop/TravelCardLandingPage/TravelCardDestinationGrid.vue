<script setup lang="ts">
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';
import { ShopProductIndex } from '@/types/Shop';

defineProps<{ products: ShopProductIndex[] }>();
</script>

<template>
  <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
    <CategoryProductCard
      v-for="(product, index) in products"
      :key="product.link"
      :product="product"
      :eager="index < 3"
      class="transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-lg"
    />
  </div>
</template>
