<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import { ShopCategoryIndex } from '@/types/Shop';
import IndexCategoryCard from '@/Components/PageSpecific/Shop/IndexCategoryCard.vue';

defineProps<{
  categories: ShopCategoryIndex[];
}>();
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading> Coeliac Sanctuary Shop</Heading>

    <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
      Welcome to the Coeliac Sanctuary Shop, here you can find a range of
      helpful items including Coeliac travel cards to help you explain Coeliac
      and the need for gluten free, available in 50 languages, we cover most of
      the world, so wherever you are visiting you can eat safely. You can also
      find other products that make living with Coeliac easier such as my pends
      and my water and freezer proof gluten free stickers and a few other bits
      and bobs!
    </p>
  </Card>

  <div class="flex flex-col space-y-4 py-2 sm:space-y-6">
    <IndexCategoryCard
      v-for="category in categories"
      :key="category.link"
      :category="category"
    />
  </div>
</template>
