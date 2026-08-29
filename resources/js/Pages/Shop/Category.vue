<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { ShopCategoryIndex, ShopProductIndex } from '@/types/Shop';
import CategoryProductCard from '@/Components/PageSpecific/Shop/CategoryProductCard.vue';
import CategoryTravelCardSearch from '@/Components/PageSpecific/Shop/CategoryTravelCardSearch.vue';
import ShopDeliveryFacts from '@/Components/PageSpecific/Shop/ShopDeliveryFacts.vue';
import Warning from '@/Components/Warning.vue';
import { Link } from '@inertiajs/vue3';

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

    <Warning v-if="category.title === 'Coeliac+ Other Allergen Travel Cards'">
      <p class="prose max-w-none">
        Have you just got Coeliac/Gluten intolerance and no other allergens? You
        might be looking for my blue
        <Link href="/shop/standard-coeliac-travel-cards">
          Standard Coeliac Travel Cards </Link
        >, rather than my yellow Coeliac+ Other Allergen cards.
      </p>
    </Warning>
  </Card>

  <CategoryTravelCardSearch v-if="category.travelCardSearch" />

  <div class="grid gap-6 sm:max-xl:grid-cols-2 lg:gap-8 xl:grid-cols-3">
    <CategoryProductCard
      v-for="(product, index) in products"
      :key="product.link"
      :product="product"
      :eager="index < 3"
      class="transition duration-300 sm:hover:-translate-y-1 sm:hover:shadow-lg"
    />
  </div>

  <ShopDeliveryFacts />
</template>
