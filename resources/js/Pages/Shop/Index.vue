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
    <Heading>
      Shop Gluten Free Travel Translation Cards & Coeliac Stickers
    </Heading>

    <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
      Whether you're travelling abroad or organising your gluten free kitchen at
      home, you'll find products I've created to make living with coeliac
      disease a little easier. My gluten free travel translation cards are
      available in more than 60 languages and help you clearly communicate your
      dietary requirements when eating out overseas. I also offer coeliac
      awareness stickers to help identify gluten free food, storage containers
      and kitchen equipment, reducing the risk of cross contamination.
    </p>

    <p class="prose max-w-none md:max-xl:prose-lg xl:prose-xl">
      Every product has been designed using my own experience of living with
      coeliac disease, with a focus on making everyday life and travel simpler,
      safer and less stressful.
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
