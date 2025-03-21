<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { ShopCategoryIndex, ShopProductIndex } from '@/types/Shop';
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';
import CategoryTravelCardSearch from '@/Components/PageSpecific/Shop/CategoryTravelCardSearch.vue';

defineProps<{
  category: ShopCategoryIndex;
  products: ShopProductIndex[];
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading
      :back-link="{
        label: 'Back to all categories',
        href: '/shop',
        position: 'top',
        direction: 'left',
      }"
    >
      {{ category.title }}
    </Heading>

    <p
      class="prose max-w-none md:max-xl:prose-lg xl:prose-xl"
      v-text="category.description"
    />
  </Card>

  <CategoryTravelCardSearch v-if="category.travelCardSearch" />

  <div
    class="grid grid-cols-1 gap-y-4 p-3 sm:max-lg:grid-cols-2 sm:gap-3 lg:grid-cols-3 2xl:p-0"
  >
    <CategoryProductCard
      v-for="product in products"
      :key="product.link"
      :product="product"
    />
  </div>
</template>
