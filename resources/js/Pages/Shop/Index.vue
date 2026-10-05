<script lang="ts" setup>
import Card from '@/Components/Card.vue';
import Heading from '@/Components/Heading.vue';
import {
  ShopCategoryIndex,
  ShopIndexReview,
  ShopPopularProduct,
} from '@/types/Shop';
import IndexCategoryCard from '@/Components/PageSpecific/Shop/IndexCategoryCard.vue';
import ShopPopularProducts from '@/Components/PageSpecific/Shop/ShopPopularProducts.vue';
import ShopCustomerReviews from '@/Components/PageSpecific/Shop/ShopCustomerReviews.vue';
import ShopDeliveryFacts from '@/Components/PageSpecific/Shop/ShopDeliveryFacts.vue';
import SubHeading from '@/Components/SubHeading.vue';
import { computed } from 'vue';

const props = defineProps<{
  categories: {
    travelCards: ShopCategoryIndex[];
    everythingElse: ShopCategoryIndex[];
  };
  digitalCards: {
    id: string;
    pageTitle: string;
    link: string;
    image: string;
  }[];
  popularProducts: ShopPopularProduct[];
  reviews: ShopIndexReview[];
}>();

const travelCards = computed((): ShopCategoryIndex[] => [
  ...props.categories.travelCards,
  ...props.digitalCards.map((card) => ({
    title: card.pageTitle,
    description: '',
    link: card.link,
    image: card.image,
    travelCardSearch: false,
  })),
]);
</script>

<template>
  <Card class="mt-3 flex flex-col space-y-4">
    <Heading>
      Shop Gluten Free Travel Translation Cards & Coeliac Stickers
    </Heading>

    <div class="prose prose-lg max-w-none md:prose-xl">
      <p>
        Whether you're travelling abroad or organising your gluten free kitchen
        at home, you'll find products I've created to make living with coeliac
        disease a little easier. My gluten free travel translation cards are
        available in more than 60 languages and help you clearly communicate
        your dietary requirements when eating out overseas. I also offer coeliac
        awareness stickers to help identify gluten free food, storage containers
        and kitchen equipment, reducing the risk of cross contamination.
      </p>

      <p>
        Every product has been designed using my own experience of living with
        coeliac disease, with a focus on making everyday life and travel
        simpler, safer and less stressful.
      </p>
    </div>
  </Card>

  <Card>
    <SubHeading>My Travel Cards</SubHeading>

    <div class="grid grid-cols-1 gap-4 py-2 md:grid-cols-2 lg:grid-cols-3">
      <IndexCategoryCard
        v-for="category in travelCards"
        :key="category.link"
        :category="category"
      />
    </div>
  </Card>

  <Card>
    <SubHeading>My Other Products</SubHeading>

    <div class="grid grid-cols-1 gap-4 py-2 md:grid-cols-2 lg:grid-cols-3">
      <IndexCategoryCard
        v-for="category in categories.everythingElse"
        :key="category.link"
        :category="category"
      />
    </div>
  </Card>

  <ShopPopularProducts
    v-if="popularProducts.length"
    :products="popularProducts"
  />

  <ShopCustomerReviews
    v-if="reviews.length"
    :reviews="reviews"
  />

  <ShopDeliveryFacts />
</template>
